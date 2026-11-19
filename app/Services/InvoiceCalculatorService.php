<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Collection;

/**
 * Service untuk perhitungan finansial invoice secara presisi di sisi server.
 * Tidak pernah mempercayai nilai yang dikirim dari frontend.
 */
class InvoiceCalculatorService
{
    /**
     * Hitung ulang seluruh total invoice dari items.
     *
     * @param  Collection<int, InvoiceItem>|array<int, InvoiceItem>  $items
     * @return array{
     *     subtotal: float,
     *     discount_amount: float,
     *     tax_amount: float,
     *     grand_total: float,
     *     line_totals: array<int, float>
     * }
     */
    public function calculate(
        Collection|array $items,
        string $discountType = 'fixed',
        float $discountValue = 0.0,
        float $taxRate = 0.0
    ): array {
        $items = $items instanceof Collection ? $items : collect($items);

        // 1. Hitung line totals & subtotal
        $lineTotals = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $qty = (float) $item->quantity;
            $price = (float) $item->unit_price;
            $lineTotal = $this->round($qty * $price);

            $lineTotals[] = $lineTotal;
            $subtotal = $this->round($subtotal + $lineTotal);
        }

        // 2. Hitung discount amount
        $discountAmount = 0.0;
        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = $this->round($subtotal * ($discountValue / 100));
            } else {
                $discountAmount = min($discountValue, $subtotal);
            }
        }

        // 3. Net before tax
        $netBeforeTax = $this->round($subtotal - $discountAmount);

        // 4. Hitung tax amount
        $taxAmount = 0.0;
        if ($taxRate > 0 && $netBeforeTax > 0) {
            $taxAmount = $this->round($netBeforeTax * ($taxRate / 100));
        }

        // 5. Grand total
        $grandTotal = $this->round($netBeforeTax + $taxAmount);

        return [
            'subtotal'         => $subtotal,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'grand_total'      => $grandTotal,
            'line_totals'      => $lineTotals,
        ];
    }

    /**
     * Hitung ulang dan update model Invoice + InvoiceItems.
     */
    public function recalculateAndSync(Invoice $invoice): Invoice
    {
        $items = $invoice->items()->get();

        $calculated = $this->calculate(
            $items,
            $invoice->discount_type,
            $invoice->discount_value,
            $invoice->tax_rate
        );

        // Update invoice totals
        $invoice->subtotal        = $calculated['subtotal'];
        $invoice->discount_amount = $calculated['discount_amount'];
        $invoice->tax_amount      = $calculated['tax_amount'];
        $invoice->grand_total     = $calculated['grand_total'];
        $invoice->save();

        // Update line totals on items
        foreach ($items as $index => $item) {
            $item->line_total = $calculated['line_totals'][$index];
            $item->save();
        }

        return $invoice->fresh();
    }

    /**
     * Pembulatan standar ke 2 desimal (banker's rounding via PHP round).
     */
    private function round(float $value): float
    {
        return round($value, 2);
    }
}