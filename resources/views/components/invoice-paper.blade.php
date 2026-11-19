@props(['invoice', 'mode' => 'preview'])

@php
    $company = config('invoice.company');
    $bankAccounts = config('invoice.bank_accounts');
    $defaultBank = collect($bankAccounts)->firstWhere('is_default', true) ?? $bankAccounts[0];
    $statuses = config('invoice.statuses');
    
    $isOverdue = $invoice->status === 'sent' && $invoice->due_date && $invoice->due_date->isPast();
    $displayStatus = $isOverdue ? 'Jatuh Tempo' : ($statuses[$invoice->status] ?? ucfirst($invoice->status));
    $statusClass = match($invoice->status) {
        'draft' => 'bg-surface-container text-secondary',
        'sent' => $isOverdue ? 'bg-error-container text-error' : 'bg-secondary-container text-on-secondary-fixed',
        'paid' => 'bg-tertiary-fixed text-on-tertiary-fixed font-semibold',
        'cancelled' => 'bg-outline-variant text-on-surface-variant',
        default => 'bg-surface-container text-secondary',
    };
    
    // Format numbers
    $format = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
    
    // Terbilang
    function terbilang($angka) {
        $bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) return $bilangan[$angka];
        if ($angka < 20) return terbilang($angka - 10) . ' Belas';
        if ($angka < 100) return terbilang($angka / 10) . ' Puluh ' . terbilang($angka % 10);
        if ($angka < 200) return 'Seratus ' . terbilang($angka - 100);
        if ($angka < 1000) return terbilang($angka / 100) . ' Ratus ' . terbilang($angka % 100);
        if ($angka < 2000) return 'Seribu ' . terbilang($angka - 1000);
        if ($angka < 1000000) return terbilang($angka / 1000) . ' Ribu ' . terbilang($angka % 1000);
        if ($angka < 1000000000) return terbilang($angka / 1000000) . ' Juta ' . terbilang($angka % 1000000);
        return terbilang($angka / 1000000000) . ' Miliar ' . terbilang($angka % 1000000000);
    }
    $terbilang = terbilang($invoice->grand_total) . ' Rupiah';
@endphp

<div class="w-full bg-surface-container-lowest rounded-lg shadow-md p-6 sm:p-10 flex flex-col justify-between text-on-surface relative" id="a4-document">
    <!-- Watermark -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.02] bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px]"></div>
    
    <div>
        <!-- Kop Resmi Perusahaan -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-space-md mb-space-md gap-space-sm bg-surface-container-low/40 p-space-sm rounded-lg">
            <div class="flex items-center gap-space-sm">
                <img alt="{{ $company['name'] }} Emblem" class="w-14 h-14 object-contain shrink-0 bg-surface-container-lowest p-1 rounded-sm shadow-xs" src="{{ $company['logo_path'] }}"/>
                <div>
                    <h2 class="font-display text-headline-lg-mobile sm:text-headline-lg uppercase font-bold text-on-surface tracking-tight">{{ $company['short_name'] }}</h2>
                    <p class="font-body-sm text-body-sm font-medium text-secondary">{{ $company['tagline'] }}</p>
                    <p class="font-label-caps text-label-caps text-secondary mt-0.5">{{ $company['address'] }} | Telp: {{ $company['phone'] }}</p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 bg-surface-container text-on-surface font-label-caps text-label-caps font-bold tracking-widest uppercase rounded">ORIGINAL INVOICE</span>
                <p class="font-label-caps text-label-caps text-secondary mt-1">NPWP: {{ $company['npwp'] }}</p>
            </div>
        </div>

        <!-- Document Header & Meta -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md mb-space-lg bg-surface-container-lowest">
            <div>
                <span class="font-label-caps text-label-caps text-secondary uppercase font-bold block mb-1">Ditagihkan Kepada (Bill To):</span>
                <div class="bg-surface-container-low p-space-sm rounded-lg">
                    <p class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $invoice->client->name }}</p>
                    <p class="font-body-sm text-body-sm text-secondary mt-1">{{ $invoice->client->address ?? '-' }}</p>
                    <div class="mt-2 pt-2 flex flex-wrap gap-x-space-md gap-y-1 font-label-caps text-label-caps text-secondary">
                        <span>NPWP: <strong class="text-on-surface">{{ $invoice->client->npwp ?? '-' }}</strong></span>
                        @if($invoice->client->email)
                            <span>Email: <strong class="text-on-surface">{{ $invoice->client->email }}</strong></span>
                        @endif
                        @if($invoice->client->phone)
                            <span>Telp: <strong class="text-on-surface">{{ $invoice->client->phone }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <span class="font-label-caps text-label-caps text-secondary uppercase font-bold block mb-1">Detail Dokumen Komersial:</span>
                <div class="bg-surface-container-low p-space-sm rounded-lg flex flex-col justify-between h-[calc(100%-24px)]">
                    <div class="flex justify-between items-center py-1">
                        <span class="font-body-sm text-body-sm text-secondary">Nomor Dokumen</span>
                        <span class="font-numeric-md text-numeric-md font-bold text-on-surface">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="font-body-sm text-body-sm text-secondary">Tanggal Terbit</span>
                        <span class="font-numeric-sm text-numeric-sm font-medium text-on-surface">{{ $invoice->issue_date->format('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="font-body-sm text-body-sm text-secondary">Batas Jatuh Tempo</span>
                        <span class="font-numeric-sm text-numeric-sm font-bold {{ $isOverdue ? 'text-error' : 'text-on-surface' }}">{{ $invoice->due_date->format('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="font-body-sm text-body-sm text-secondary">Metode Pembayaran</span>
                        <span class="font-label-caps text-label-caps font-semibold text-on-surface uppercase">Term of Payment (TOP {{ config('invoice.defaults.payment_terms') }} Hari)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="w-full overflow-x-auto mb-space-md">
            <table class="w-full text-left min-w-[560px]">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-caps text-label-caps uppercase">
                        <th class="py-2.5 px-3 rounded-l-md w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Deskripsi Barang & Spesifikasi Mutu</th>
                        <th class="py-2.5 px-3 text-center w-16">Qty</th>
                        <th class="py-2.5 px-3 text-center w-16">Sat</th>
                        <th class="py-2.5 px-3 text-right w-28">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right rounded-r-md w-32">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody class="font-body-md text-body-md divide-y-0">
                    @foreach($invoice->items as $index => $item)
                        <tr class="{{ $loop->odd ? 'bg-surface-container-low/30' : '' }} hover:bg-surface-container-low transition-colors">
                            <td class="py-3 px-3 text-center font-numeric-sm text-numeric-sm text-secondary align-top">{{ $loop->iteration }}</td>
                            <td class="py-3 px-3 align-top">
                                <div class="font-semibold text-on-surface">{{ $item->description }}</div>
                            </td>
                            <td class="py-3 px-3 text-center font-numeric-md text-numeric-md align-top">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center font-label-caps text-label-caps text-secondary align-top">Unit</td>
                            <td class="py-3 px-3 text-right font-numeric-md text-numeric-md text-secondary align-top">{{ $format($item->unit_price) }}</td>
                            <td class="py-3 px-3 text-right font-numeric-md text-numeric-md font-semibold text-on-surface align-top">{{ $format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Calculation -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md mb-space-lg bg-surface-container-low p-space-sm rounded-lg">
            <div class="md:col-span-6 flex flex-col justify-between">
                <div>
                    <span class="font-label-caps text-label-caps text-secondary uppercase font-bold block mb-1">Terbilang (In Words):</span>
                    <div class="bg-surface-container-lowest p-space-sm rounded-sm font-body-sm text-body-sm italic text-on-surface leading-snug">
                        {{ $terbilang }}
                    </div>
                </div>
                <div class="mt-space-sm">
                    <span class="font-label-caps text-label-caps text-secondary uppercase font-bold block mb-1">Catatan Transaksi:</span>
                    <p class="font-body-sm text-body-sm text-secondary leading-tight">
                        {{ $invoice->notes ?? 'Pembayaran wajib menyertakan nomor faktur ' . $invoice->invoice_number . ' pada berita transfer.' }}
                    </p>
                </div>
            </div>
            <div class="md:col-span-6 flex flex-col gap-1.5 justify-center">
                <div class="flex justify-between items-center text-body-sm font-body-sm text-secondary">
                    <span>Subtotal Barang / Jasa</span>
                    <span class="font-numeric-md text-numeric-md text-on-surface">{{ $format($invoice->subtotal) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between items-center text-body-sm font-body-sm text-secondary">
                        <span>Diskon ({{ $invoice->discount_type === 'percent' ? $invoice->discount_value . '%' : $format($invoice->discount_value) }})</span>
                        <span class="font-numeric-md text-numeric-md text-error">- {{ $format($invoice->discount_amount) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center text-body-sm font-body-sm text-secondary">
                    <span>Dasar Pengenaan Pajak (DPP)</span>
                    <span class="font-numeric-md text-numeric-md text-on-surface">{{ $format($invoice->subtotal - $invoice->discount_amount) }}</span>
                </div>
                <div class="flex justify-between items-center text-body-sm font-body-sm text-secondary">
                    <span>PPN {{ $invoice->tax_rate }}% (Faktur Pajak Terbit)</span>
                    <span class="font-numeric-md text-numeric-md text-on-surface">{{ $format($invoice->tax_amount) }}</span>
                </div>
                <div class="h-0.5 bg-outline-variant/40 my-1"></div>
                <div class="flex justify-between items-center bg-surface-container-lowest p-2 rounded-sm">
                    <span class="font-label-caps text-label-caps uppercase font-bold text-on-surface">Sisa Tagihan Net:</span>
                    <span class="font-numeric-hero-mobile text-numeric-hero-mobile font-bold text-on-surface">{{ $format($invoice->grand_total) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Footer -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md pt-space-sm items-end bg-surface-container-lowest">
        <div class="p-space-sm bg-surface-container-low rounded-lg">
            <span class="font-label-caps text-label-caps text-secondary uppercase font-bold block mb-1">Rekening Pembayaran Resmi:</span>
            <div class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $defaultBank['bank_name'] }}</div>
            <div class="font-numeric-md text-numeric-md font-bold tracking-wider text-primary mt-0.5">{{ $defaultBank['account_no'] }}</div>
            <div class="font-body-sm text-body-sm text-secondary mt-0.5">a.n {{ $defaultBank['account_name'] }}</div>
            <div class="font-label-caps text-label-caps text-secondary mt-1">{{ $defaultBank['branch'] }}</div>
        </div>
        <div class="flex flex-col items-center sm:items-end text-center sm:text-right">
            <p class="font-label-caps text-label-caps text-secondary uppercase">{{ $invoice->issue_date->format('d F Y') }}</p>
            <p class="font-label-caps text-label-caps font-bold text-on-surface uppercase mb-3">{{ $company['short_name'] }}</p>
            <!-- Signature & Stamp -->
            <div class="relative w-44 h-20 flex items-center justify-center">
                <div class="absolute inset-0 flex items-center justify-center opacity-85 rotate-[-8deg] pointer-events-none">
                    <div class="w-36 h-16 rounded-lg bg-error-container/60 p-1 flex flex-col items-center justify-center text-error">
                        <span class="font-label-caps text-label-caps font-black tracking-widest text-[9px] uppercase leading-tight">{{ $company['short_name'] }}</span>
                        <span class="font-display font-bold text-[11px] tracking-tight uppercase">AUTHORIZED</span>
                        <span class="font-label-caps text-label-caps font-semibold text-[8px] uppercase">FINANCE & TAX DEPT</span>
                    </div>
                </div>
                <svg class="w-32 h-14 text-on-surface/80 relative z-10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2" viewbox="0 0 160 60">
                    <path d="M10 45 C 30 10, 40 50, 55 25 C 65 5, 75 40, 90 20 C 110 5, 120 50, 145 30"></path>
                    <path d="M40 38 L 130 38" stroke-dasharray="3 3" stroke-width="1.5"></path>
                </svg>
            </div>
            <div class="mt-2 text-center sm:text-right">
                <span class="font-body-sm text-body-sm font-bold text-on-surface underline">Arya Danuningrat, S.E.</span>
                <span class="block font-label-caps text-label-caps text-secondary">Chief Financial Officer</span>
            </div>
        </div>
    </div>
</div>