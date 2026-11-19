<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur {{ $invoice->invoice_number }} - Mahameru Supplies</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 25mm 15mm;
            @bottom-center {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 8pt;
                color: #666;
            }
        }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        .invoice-container { width: 100%; max-width: 100%; }
        .header { 
            border-bottom: 2px solid #1a1a1a; 
            padding-bottom: 8px; 
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .company-block { display: flex; justify-content: space-between; gap: 20px; }
        .company-info h1 {
            font-size: 16pt;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }
        .company-tagline {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .company-meta { font-size: 7.5pt; color: #444; line-height: 1.5; }
        .company-meta strong { color: #1a1a1a; }
        .invoice-meta { text-align: right; min-width: 180px; }
        .invoice-meta .label { font-size: 7pt; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .invoice-meta .value { font-size: 10pt; font-weight: 700; color: #1a1a1a; font-family: 'Courier New', monospace; }
        .invoice-meta .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .badge-draft { background: #e0e0e0; color: #666; }
        .badge-sent { background: #e3f2fd; color: #1565c0; }
        .badge-overdue { background: #fdeaea; color: #c62828; }
        .badge-paid { background: #e8f5e9; color: #2e7d32; }
        .badge-cancelled { background: #e0e0e0; color: #666; }
        
        .parties { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 16px; page-break-inside: avoid; }
        .party-block { flex: 1; }
        .party-block h3 { font-size: 8pt; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 6px 0; }
        .party-block .name { font-size: 10pt; font-weight: 700; color: #1a1a1a; margin: 0 0 4px 0; }
        .party-block .detail { font-size: 8pt; color: #444; line-height: 1.5; margin: 2px 0; }
        .party-block .detail strong { color: #1a1a1a; min-width: 50px; display: inline-block; }
        
        .table-wrapper { margin-bottom: 16px; page-break-inside: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        thead th {
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
        }
        thead th.num { text-align: right; width: 45px; }
        thead th.price { text-align: right; width: 70px; }
        thead th.total { text-align: right; width: 80px; }
        tbody td {
            padding: 6px 4px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.desc { font-size: 8.5pt; line-height: 1.4; }
        tbody td.price { text-align: right; font-variant-numeric: tabular-nums; font-size: 8pt; }
        tbody td.total { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; font-size: 8.5pt; }
        tbody tr:last-child td { border-bottom: 1px solid #ddd; }
        tfoot td { padding: 4px 0; font-size: 8pt; vertical-align: top; }
        tfoot td.label { text-align: right; color: #444; padding-right: 8px; }
        tfoot td.value { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
        tfoot tr.total-row td { font-size: 9.5pt; font-weight: 700; color: #1a1a1a; border-top: 1px solid #1a1a1a; padding-top: 8px; }
        tfoot tr.total-row td.value { font-size: 11pt; }
        
        .totals { width: 100%; float: right; clear: both; }
        
        .footer-section { 
            margin-top: 20px; 
            padding-top: 12px; 
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #444;
            page-break-inside: avoid;
        }
        .bank-info { margin-bottom: 12px; }
        .bank-info h4 { font-size: 8pt; font-weight: 700; color: #888; text-transform: uppercase; margin: 0 0 6px 0; }
        .bank-row { display: flex; gap: 12px; margin: 3px 0; flex-wrap: wrap; }
        .bank-row .label { min-width: 80px; color: #666; font-size: 7.5pt; }
        .bank-row .value { font-weight: 600; color: #1a1a1a; font-family: 'Courier New', monospace; font-size: 8.5pt; }
        
        .notes { margin-top: 10px; padding: 8px; background: #fafafa; border: 1px dashed #ddd; border-radius: 3px; }
        .notes strong { color: #1a1a1a; }
        
        .terbilang { margin-top: 10px; font-size: 8pt; font-style: italic; color: #333; line-height: 1.5; }
        .terbilang strong { font-style: normal; color: #1a1a1a; }
        
        .signature-block { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 30px; 
            padding-top: 12px;
            border-top: 1px solid #eee;
            page-break-inside: avoid;
        }
        .sig-col { text-align: center; width: 45%; }
        .sig-col .label { font-size: 7.5pt; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 40px; }
        .sig-col .name { font-size: 9pt; font-weight: 600; color: #1a1a1a; }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(0,0,0,0.03);
            font-weight: 700;
            pointer-events: none;
            z-index: -1;
            white-space: nowrap;
        }
        
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="watermark">MAHAMERU SUPPLIES</div>
    
    <div class="invoice-container">
        <!-- HEADER -->
        <header class="header">
            <div class="company-block">
                <div class="company-info">
                    <h1>{{ config('invoice.company.name') }}</h1>
                    <p class="company-tagline">{{ config('invoice.company.tagline') }}</p>
                    <div class="company-meta">
                        <div><strong>NPWP:</strong> {{ config('invoice.company.npwp') }}</div>
                        <div><strong>Alamat:</strong> {{ config('invoice.company.address') }}</div>
                        <div><strong>Telepon:</strong> {{ config('invoice.company.phone') }} | <strong>Email:</strong> {{ config('invoice.company.email') }}</div>
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="label">Nomor Faktur</div>
                    <div class="value">{{ $invoice->invoice_number }}</div>
                    <div class="label" style="margin-top: 8px;">Tanggal Terbit</div>
                    <div class="value">{{ $invoice->issue_date->format('d F Y') }}</div>
                    <div class="label" style="margin-top: 8px;">Tempo Bayar</div>
                    <div class="value">{{ $invoice->due_date->format('d F Y') }}</div>
                    @php
                        $isOverdue = $invoice->status === 'sent' && $invoice->due_date && $invoice->due_date->isPast();
                        $statusClass = match($invoice->status) {
                            'draft' => 'badge-draft',
                            'sent' => $isOverdue ? 'badge-overdue' : 'badge-sent',
                            'paid' => 'badge-paid',
                            'cancelled' => 'badge-cancelled',
                            default => 'badge-draft',
                        };
                        $statusLabel = match($invoice->status) {
                            'draft' => 'DRAFT',
                            'sent' => $isOverdue ? 'JATUH TEMPO' : 'TERKIRIM',
                            'paid' => 'LUNAS',
                            'cancelled' => 'DIBATALKAN',
                            default => strtoupper($invoice->status),
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </header>

        <!-- PARTIES -->
        <section class="parties">
            <div class="party-block">
                <h3>Dari / Penjual</h3>
                <div class="name">{{ config('invoice.company.name') }}</div>
                <div class="detail">{{ config('invoice.company.address') }}</div>
                <div class="detail"><strong>NPWP:</strong> {{ config('invoice.company.npwp') }}</div>
            </div>
            <div class="party-block" style="text-align: right;">
                <h3>Kepada / Pembeli</h3>
                <div class="name">{{ $invoice->client->name }}</div>
                <div class="detail">{{ $invoice->client->address }}</div>
                @if($invoice->client->npwp)
                    <div class="detail"><strong>NPWP:</strong> {{ $invoice->client->npwp }}</div>
                @endif
                @if($invoice->client->email)
                    <div class="detail"><strong>Email:</strong> {{ $invoice->client->email }}</div>
                @endif
            </div>
        </section>

        <!-- ITEMS TABLE -->
        <section class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="num">No</th>
                        <th style="width: 45%;">Uraian Barang / Jasa</th>
                        <th class="num" style="width: 12%;">Kuantitas</th>
                        <th class="price">Harga Satuan</th>
                        <th class="total">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="num">{{ $index + 1 }}</td>
                            <td class="desc">{{ $item->description }}</td>
                            <td class="num">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td class="price">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="total">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="label">Subtotal</td>
                        <td class="value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->discount_value > 0)
                        <tr>
                            <td colspan="4" class="label">Diskon ({{ $invoice->discount_type === 'percent' ? $invoice->discount_value . '%' : 'Rp ' . number_format($invoice->discount_value, 0, ',', '.') }})</td>
                            <td class="value" style="color: #c62828;">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="label">PPN ({{ number_format($invoice->tax_rate, 2, ',', '.') }}%)</td>
                        <td class="value">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" class="label">TOTAL TAGIHAN</td>
                        <td class="value">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- FOOTER: BANK, NOTES, TERBILANG -->
        <footer class="footer-section">
            <div class="bank-info">
                <h4>Instruksi Pembayaran & Rekening</h4>
                @foreach(config('invoice.company.bank_accounts') as $bank)
                    <div class="bank-row">
                        <span class="label">{{ $bank['bank_name'] }}</span>
                        <span class="value">{{ $bank['account_no'] }} a.n. {{ $bank['account_name'] }}</span>
                    </div>
                @endforeach
            </div>

            @if($invoice->notes)
                <div class="notes">
                    <strong>Catatan:</strong> {{ $invoice->notes }}
                </div>
            @endif

            <div class="terbilang">
                <strong>Terbilang: </strong>{{ \App\Helpers\InvoiceHelper::terbilang($invoice->grand_total) }} Rupiah
            </div>
        </footer>

        <!-- SIGNATURE -->
        <div class="signature-block">
            <div class="sig-col">
                <div class="label">Penerima / Klien</div>
                <div class="name">(_________________________)</div>
                <div style="font-size: 7pt; color: #888; margin-top: 4px;">{{ $invoice->client->name }}</div>
            </div>
            <div class="sig-col">
                <div class="label">Hormat Kami,<br>{{ config('invoice.company.name') }}</div>
                <div class="name">(_________________________)</div>
                <div style="font-size: 7pt; color: #888; margin-top: 4px;">{{ config('invoice.company.director_name') ?? 'Direktur' }}</div>
            </div>
        </div>
    </div>
</body>
</html>