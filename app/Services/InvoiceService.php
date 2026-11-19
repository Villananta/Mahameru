<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service layer untuk operasi CRUD Invoice.
 * Mengenkapsulasi logika bisnis: validasi status, transaksi database,
 * sinkronisasi items, dan perhitungan ulang.
 */
class InvoiceService
{
    public function __construct(
        protected InvoiceNumberService $numberService,
        protected InvoiceCalculatorService $calculatorService
    ) {}

    /**
     * Buat invoice baru beserta items-nya dalam satu transaksi.
     *
     * @param  array{
     *     client_id: int,
     *     issue_date: string,
     *     due_date: string,
     *     discount_type?: string,
     *     discount_value?: float,
     *     tax_rate?: float,
     *     status?: string,
     *     notes?: string,
     *     items: array<int, array{description: string, quantity: float, unit_price: float}>
     * }  $data
     * @return Invoice
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // 1. Generate nomor invoice
            $invoiceNumber = $this->numberService->generate();

            // 2. Siapkan data invoice (totals akan dihitung ulang nanti)
            $invoiceData = [
                'invoice_number' => $invoiceNumber,
                'client_id'      => $data['client_id'],
                'issue_date'     => $data['issue_date'],
                'due_date'       => $data['due_date'],
                'discount_type'  => $data['discount_type'] ?? config('invoice.defaults.discount_type'),
                'discount_value' => $data['discount_value'] ?? config('invoice.defaults.discount_value'),
                'tax_rate'       => $data['tax_rate'] ?? config('invoice.defaults.tax_rate'),
                'status'         => $data['status'] ?? 'draft',
                'notes'          => $data['notes'] ?? null,
                'subtotal'       => 0,
                'discount_amount'=> 0,
                'tax_amount'     => 0,
                'grand_total'    => 0,
            ];

            $invoice = Invoice::create($invoiceData);

            // 3. Create items
            $itemsData = $data['items'] ?? [];
            foreach ($itemsData as $index => $itemData) {
                InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'description'  => $itemData['description'],
                    'quantity'     => $itemData['quantity'],
                    'unit_price'   => $itemData['unit_price'],
                    'line_total'   => 0, // akan dihitung ulang
                ]);
            }

            // 4. Recalculate totals
            return $this->calculatorService->recalculateAndSync($invoice);
        });
    }

    /**
     * Update invoice (header + items) dalam transaksi.
     * Akan gagal jika invoice sudah locked (paid/cancelled).
     *
     * @param  array{
     *     client_id?: int,
     *     issue_date?: string,
     *     due_date?: string,
     *     discount_type?: string,
     *     discount_value?: float,
     *     tax_rate?: float,
     *     status?: string,
     *     notes?: string,
     *     items: array<int, array{id?: int, description: string, quantity: float, unit_price: float}>
     * }  $data
     * @return Invoice
     * @throws InvalidArgumentException jika invoice terkunci
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->is_locked) {
            throw new InvalidArgumentException(
                'Invoice sudah terkunci (status: ' . $invoice->display_status . ') dan tidak bisa diedit.'
            );
        }

        return DB::transaction(function () use ($invoice, $data) {
            // 1. Update header fields
            $fillable = [
                'client_id',
                'issue_date',
                'due_date',
                'discount_type',
                'discount_value',
                'tax_rate',
                'notes',
            ];

            foreach ($fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $invoice->$field = $data[$field];
                }
            }
            $invoice->save();

            // 2. Sync items: delete removed, update existing, create new
            $incomingItems = $data['items'] ?? [];
            $incomingIds = collect($incomingItems)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->toArray();

            // Delete items not in incoming
            $invoice->items()
                ->whereNotIn('id', $incomingIds)
                ->delete();

            // Upsert items
            foreach ($incomingItems as $itemData) {
                if (isset($itemData['id'])) {
                    $item = InvoiceItem::findOrFail($itemData['id']);
                    $item->description = $itemData['description'];
                    $item->quantity    = $itemData['quantity'];
                    $item->unit_price  = $itemData['unit_price'];
                    $item->save();
                } else {
                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'description' => $itemData['description'],
                        'quantity'    => $itemData['quantity'],
                        'unit_price'  => $itemData['unit_price'],
                        'line_total'  => 0,
                    ]);
                }
            }

            // 3. Recalculate
            return $this->calculatorService->recalculateAndSync($invoice);
        });
    }

    /**
     * Update status invoice dengan validasi transisi.
     *
     * @param  string  $status  draft|sent|paid|cancelled
     * @throws InvalidArgumentException jika transisi tidak valid
     */
    public function updateStatus(Invoice $invoice, string $status): Invoice
    {
        $validStatuses = array_keys(config('invoice.statuses'));

        if (! in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Status tidak valid: {$status}");
        }

        // Validasi transisi status
        $this->validateStatusTransition($invoice->status, $status);

        $invoice->status = $status;
        $invoice->save();

        return $invoice->fresh();
    }

    /**
     * Validasi apakah transisi status diizinkan.
     */
    private function validateStatusTransition(string $from, string $to): void
    {
        // Draft -> bisa ke sent, cancelled
        // Sent -> bisa ke paid, cancelled, draft
        // Paid -> tidak bisa ke status lain (locked)
        // Cancelled -> tidak bisa ke status lain (locked)

        if (in_array($from, ['paid', 'cancelled'], true)) {
            throw new InvalidArgumentException(
                "Tidak bisa mengubah status dari '{$from}' (invoice terkunci)."
            );
        }

        // Valid transisi dari draft/sent
        $allowed = [
            'draft' => ['sent', 'cancelled'],
            'sent'  => ['paid', 'cancelled', 'draft'],
        ];

        if (! in_array($to, $allowed[$from] ?? [], true)) {
            throw new InvalidArgumentException(
                "Transisi status dari '{$from}' ke '{$to}' tidak diizinkan."
            );
        }
    }

    /**
     * Hapus invoice (soft delete via status cancelled).
     * Menggunakan status cancelled agar riwayat tetap ada.
     */
    public function deleteInvoice(Invoice $invoice): Invoice
    {
        if ($invoice->is_locked) {
            throw new InvalidArgumentException(
                'Invoice sudah terkunci dan tidak bisa dihapus.'
            );
        }

        return $this->updateStatus($invoice, 'cancelled');
    }
}