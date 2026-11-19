<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceStatusRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of invoices with KPI summary, filters, search, pagination.
     */
    public function index(Request $request): View
    {
        $query = Invoice::query()
            ->with(['client:id,name' => function ($q) {
                $q->withTrashed(); // include soft-deleted clients
            }])
            ->latestFirst();

        // Search
        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        // Status filter (including virtual 'overdue')
        if ($request->filled('status') && $request->string('status') !== 'all') {
            $query->status($request->string('status')->toString());
        }

        // KPI aggregates (before pagination)
        $kpis = $this->getKpis();

        // Pagination
        $invoices = $query->paginate(10)->withQueryString();

        return view('invoices.index', compact('invoices', 'kpis'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $clients = Client::active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'address', 'npwp']);

        $defaults = config('invoice.defaults');

        return view('invoices.form', compact('clients', 'defaults'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Faktur berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal membuat faktur: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified invoice (detail + A4 preview).
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['client' => fn($q) => $q->withTrashed(), 'items']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        if ($invoice->is_locked) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Invoice sudah terkunci (status: ' . $invoice->display_status . ') dan tidak bisa diedit.');
        }

        $invoice->load(['client' => fn($q) => $q->withTrashed(), 'items']);

        $clients = Client::active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'address', 'npwp']);

        $defaults = config('invoice.defaults');

        return view('invoices.form', compact('invoice', 'clients', 'defaults'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->is_locked) {
            return back()
                ->withErrors(['error' => 'Invoice sudah terkunci (status: ' . $invoice->display_status . ') dan tidak bisa diedit.']);
        }

        try {
            $invoice = $this->invoiceService->updateInvoice($invoice, $request->validated());

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Faktur berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui faktur: ' . $e->getMessage()]);
        }
    }

    /**
     * Update invoice status (quick action).
     */
    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        try {
            $invoice = $this->invoiceService->updateStatus($invoice, $request->validated('status'));

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status berhasil diperbarui.',
                    'invoice' => [
                        'id'           => $invoice->id,
                        'status'       => $invoice->status,
                        'display_status' => $invoice->display_status,
                        'is_locked'    => $invoice->is_locked,
                    ],
                ]);
            }

            return back()->with('success', 'Status faktur berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate and download PDF.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client' => fn($q) => $q->withTrashed(), 'items']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.pdf', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Faktur-{$invoice->invoice_number}.pdf");
    }

    /**
     * Get KPI summary for dashboard.
     */
    private function getKpis(): array
    {
        $totalOutstanding = Invoice::whereIn('status', ['sent', 'overdue'])
            ->sum('grand_total');

        $totalPaid = Invoice::where('status', 'paid')
            ->sum('grand_total');

        $overdueCount = Invoice::where('status', 'sent')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $draftCount = Invoice::where('status', 'draft')->count();

        return [
            'total_outstanding' => $totalOutstanding,
            'total_paid'        => $totalPaid,
            'overdue_count'     => $overdueCount,
            'draft_count'       => $draftCount,
        ];
    }
}