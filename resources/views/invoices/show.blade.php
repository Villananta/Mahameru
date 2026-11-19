@extends('layouts.app')

@php
    $title = 'Detail Faktur #' . $invoice->invoice_number;
    $topbarTitle = 'Detail Faktur';
    $topbarSubtitle = $invoice->invoice_number;
    $isOverdue = $invoice->status === 'sent' && $invoice->due_date && $invoice->due_date->isPast();
    $format = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

@section('content')
<div class="flex flex-col w-full pb-16">
    <!-- Top Command & Status Deck -->
    <div class="sticky top-16 z-30 bg-surface-container-lowest shadow-sm rounded-lg p-space-sm mb-space-md">
        <div class="flex flex-wrap items-center justify-between gap-space-xs mb-space-xs">
            <div class="flex items-center gap-space-xs min-w-0">
                <a class="w-9 h-9 flex items-center justify-center rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface" href="{{ route('invoices.index') }}">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-space-xs">
                        <span class="font-numeric-md text-numeric-md font-bold text-on-surface truncate">#{{ $invoice->invoice_number }}</span>
                        <x-status-badge :status="$invoice->status" :dueDate="$invoice->due_date" />
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-space-xs ml-auto">
                <button class="relative group h-9 px-3 rounded-lg bg-surface-container-low text-on-surface flex items-center gap-1.5 text-body-sm font-body-sm font-medium hover:bg-surface-container transition-colors" id="btn-edit-invoice" {{ $invoice->is_locked ? 'disabled' : '' }} onclick="{{ $invoice->is_locked ? 'return' : 'window.location.href=\'' . route('invoices.edit', $invoice) . '\''' }}">
                    <span class="material-symbols-outlined text-[18px]">edit_document</span>
                    <span class="hidden sm:inline">{{ $invoice->is_locked ? 'Terkunci' : 'Edit' }}</span>
                </button>
                <button class="h-9 px-3 rounded-lg bg-surface-container-low text-on-surface flex items-center gap-1.5 text-body-sm font-body-sm font-medium hover:bg-surface-container transition-colors" onclick="toggleStatusSheet()">
                    <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                    <span class="hidden sm:inline">Ubah Status</span>
                </button>
                @if($invoice->client->phone)
                    <a class="h-9 px-3 rounded-lg bg-surface-container text-on-tertiary-container flex items-center gap-1.5 text-body-sm font-body-sm font-medium hover:bg-surface-container-high transition-colors" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $invoice->client->phone) }}?text=Halo%20{{ urlencode($invoice->client->name) }}%2C%20berikut%20kami%20lampirkan%20Faktur%20Mahameru%20Supplies%20No%3A%20{{ urlencode($invoice->invoice_number) }}%20sebesar%20{{ urlencode($format($invoice->grand_total)) }}%20yang%20telah%20jatuh%20tempo." target="_blank">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                        <span class="hidden sm:inline">Kirim WA</span>
                    </a>
                @endif
                <button class="h-9 px-3.5 rounded-lg bg-primary text-on-primary flex items-center gap-1.5 text-body-sm font-body-sm font-semibold hover:bg-primary-container transition-all active:scale-[0.98]" onclick="triggerPdfDownload()">
                    <span class="material-symbols-outlined text-[18px]" id="pdf-icon">download</span>
                    <span id="pdf-btn-text">Unduh PDF A4</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Operational Snapshot -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm mb-space-md">
        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm">
            <div class="text-secondary font-label-caps text-label-caps uppercase tracking-wider mb-1">Total Tagihan {{ $isOverdue ? 'Tertunggak' : 'Aktif' }}</div>
            <div class="font-numeric-hero-mobile text-numeric-hero-mobile font-bold text-on-surface">{{ $format($invoice->grand_total) }}</div>
            @if($isOverdue)
                <div class="text-error font-body-sm text-body-sm flex items-center gap-1 mt-1 font-medium">
                    <span class="material-symbols-outlined text-[16px]">warning</span>
                    Lewat {{ $invoice->due_date->diffInDays(now()) }} hari dari batas akhir
                </div>
            @endif
        </div>
        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm">
            <div class="text-secondary font-label-caps text-label-caps uppercase tracking-wider mb-1">Target Pembayaran</div>
            <div class="font-headline-sm text-headline-sm text-on-surface">{{ $invoice->due_date->format('d F Y') }}</div>
            <div class="text-secondary font-body-sm text-body-sm mt-1">Diterbitkan: {{ $invoice->issue_date->format('d M Y') }} (Tempo {{ config('invoice.defaults.payment_terms') }} Hari)</div>
        </div>
        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm">
            <div class="text-secondary font-label-caps text-label-caps uppercase tracking-wider mb-1">Entitas Klien</div>
            <div class="font-headline-sm text-headline-sm text-on-surface truncate">{{ $invoice->client->name }}</div>
            <div class="text-secondary font-body-sm text-body-sm mt-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">business</span>
                UP: {{ $invoice->client->email ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Viewport Controls -->
    <div class="flex items-center justify-between bg-surface-container-low px-space-sm py-2 rounded-lg mb-space-sm">
        <div class="flex items-center gap-space-xs">
            <span class="material-symbols-outlined text-secondary text-[18px]">document_scanner</span>
            <span class="font-label-caps text-label-caps text-secondary font-semibold uppercase">Pratinjau Kertas A4 (1:1.414)</span>
        </div>
        <div class="flex items-center gap-1">
            <button class="px-2.5 py-1 rounded bg-surface-container-lowest text-on-surface font-body-sm text-body-sm font-semibold shadow-xs" id="tab-btn-preview" onclick="setPaperView('preview')">
                Pratinjau Cetak
            </button>
            <button class="px-2.5 py-1 rounded text-secondary hover:text-on-surface font-body-sm text-body-sm font-medium" id="tab-btn-blade" onclick="setPaperView('blade')">
                Blade Template
            </button>
            <div class="h-4 w-px bg-outline-variant mx-1"></div>
            <button class="p-1 rounded bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center" id="btn-zoom-toggle" onclick="togglePaperZoom()" title="Skala Pas Layar / 100%">
                <span class="material-symbols-outlined text-[18px]" id="zoom-icon">fit_screen</span>
            </button>
        </div>
    </div>

    <!-- Blade View Panel -->
    <div class="hidden w-full bg-inverse-surface rounded-lg p-space-md text-inverse-on-surface shadow-md mb-space-md" id="blade-view-panel">
        <div class="flex items-center justify-between pb-space-sm mb-space-sm">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary-fixed text-[20px]">code</span>
                <span class="font-label-caps text-label-caps text-tertiary-fixed font-bold">resources/views/invoices/pdf.blade.php</span>
            </div>
            <button class="h-7 px-2.5 rounded bg-surface-container-lowest/10 hover:bg-surface-container-lowest/20 text-inverse-on-surface text-body-sm font-body-sm flex items-center gap-1" onclick="copyBladeCode()">
                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                <span id="copy-btn-text">Salin Snippet</span>
            </button>
        </div>
        <pre class="font-numeric-sm text-numeric-sm overflow-x-auto p-space-sm bg-black/40 rounded text-slate-200 leading-relaxed max-h-96"><code><x-invoice-paper :invoice="$invoice" /></code></pre>
    </div>

    <!-- A4 Document Rendering -->
    <div class="w-full overflow-x-auto flex justify-center py-space-xs" id="paper-outer-stage">
        <div class="transition-transform duration-200 origin-top flex justify-center w-full max-w-[820px]" id="paper-viewport">
            <x-invoice-paper :invoice="$invoice" />
        </div>
    </div>

    <!-- Status Mutation Bottom Sheet -->
    <x-bottom-sheet id="status-sheet" title="Ubah Status Faktur" subtitle="{{ $invoice->invoice_number }} - {{ $invoice->client->name }}" width="max-w-md">
        <p class="font-body-sm text-body-sm text-secondary mb-space-sm">
            Pembaruan status akan tercatat pada audit trail transaksi dan mempengaruhi rekonsiliasi kas masuk.
        </p>
        <form id="status-form">
            <div class="space-y-space-xs mb-space-md">
                <label class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer">
                    <div class="flex items-center gap-space-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-error"></span>
                        <div>
                            <div class="font-body-md text-body-md font-semibold text-on-surface">Jatuh Tempo</div>
                            <div class="font-body-sm text-body-sm text-secondary">Status saat ini {{ $isOverdue ? '(Lewat ' . $invoice->due_date->diffInDays(now()) . ' hari)' : '' }}</div>
                        </div>
                    </div>
                    <input type="radio" name="invoice_status" value="sent" {{ $invoice->status === 'sent' ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-0"/>
                </label>
                <label class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer">
                    <div class="flex items-center gap-space-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-on-tertiary-container"></span>
                        <div>
                            <div class="font-body-md text-body-md font-semibold text-on-surface">Lunas Penuh</div>
                            <div class="font-body-sm text-body-sm text-secondary">Dana {{ $format($invoice->grand_total) }} sudah masuk rekening</div>
                        </div>
                    </div>
                    <input type="radio" name="invoice_status" value="paid" {{ $invoice->status === 'paid' ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-0"/>
                </label>
                <label class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer">
                    <div class="flex items-center gap-space-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
                        <div>
                            <div class="font-body-md text-body-md font-semibold text-on-surface">Dibatalkan / Void</div>
                            <div class="font-body-sm text-body-sm text-secondary">Batalkan pesanan & terbitkan credit note</div>
                        </div>
                    </div>
                    <input type="radio" name="invoice_status" value="cancelled" {{ $invoice->status === 'cancelled' ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-0"/>
                </label>
                @if($invoice->status === 'draft')
                    <label class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container cursor-pointer">
                        <div class="flex items-center gap-space-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-surface-container"></span>
                            <div>
                                <div class="font-body-md text-body-md font-semibold text-on-surface">Kembali ke Draft</div>
                                <div class="font-body-sm text-body-sm text-secondary">Batalkan pengiriman, simpan sebagai draft</div>
                            </div>
                        </div>
                        <input type="radio" name="invoice_status" value="draft" {{ $invoice->status === 'draft' ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-0"/>
                    </label>
                @endif
            </div>
            <div class="flex items-center gap-space-xs">
                <button type="button" class="w-1/2 h-11 rounded-lg bg-surface-container-low text-on-surface font-body-lg text-body-lg font-semibold hover:bg-surface-container" onclick="toggleStatusSheet()">
                    Batal
                </button>
                <button type="submit" class="w-1/2 h-11 rounded-lg bg-primary text-on-primary font-body-lg text-body-lg font-semibold hover:bg-primary-container">
                    Simpan Status
                </button>
            </div>
        </form>
    </x-bottom-sheet>

    <!-- Notification Toast -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-inverse-surface text-inverse-on-surface px-4 py-2.5 rounded-lg shadow-xl font-body-sm text-body-sm flex items-center gap-2 transition-all opacity-0 pointer-events-none" id="toast">
        <span class="material-symbols-outlined text-tertiary-fixed text-[18px]" id="toast-icon">check_circle</span>
        <span id="toast-message">Notifikasi</span>
    </div>
</div>

@push('scripts')
<script>
    let isZoomFit = false;

    function togglePaperZoom() {
        const viewport = document.getElementById('paper-viewport');
        const zoomIcon = document.getElementById('zoom-icon');
        isZoomFit = !isZoomFit;
        
        if (isZoomFit) {
            viewport.style.transform = 'scale(0.85)';
            zoomIcon.textContent = 'zoom_in';
            showToast('Tampilan disesuaikan dengan viewport mobile');
        } else {
            viewport.style.transform = 'scale(1)';
            zoomIcon.textContent = 'fit_screen';
            showToast('Skala normal 100%');
        }
    }

    function setPaperView(mode) {
        const bladePanel = document.getElementById('blade-view-panel');
        const paperStage = document.getElementById('paper-outer-stage');
        const tabPreview = document.getElementById('tab-btn-preview');
        const tabBlade = document.getElementById('tab-btn-blade');

        if (mode === 'blade') {
            bladePanel.classList.remove('hidden');
            paperStage.classList.add('hidden');
            tabBlade.className = 'px-2.5 py-1 rounded bg-surface-container-lowest text-on-surface font-body-sm text-body-sm font-semibold shadow-xs';
            tabPreview.className = 'px-2.5 py-1 rounded text-secondary hover:text-on-surface font-body-sm text-body-sm font-medium';
        } else {
            bladePanel.classList.add('hidden');
            paperStage.classList.remove('hidden');
            tabPreview.className = 'px-2.5 py-1 rounded bg-surface-container-lowest text-on-surface font-body-sm text-body-sm font-semibold shadow-xs';
            tabBlade.className = 'px-2.5 py-1 rounded text-secondary hover:text-on-surface font-body-sm text-body-sm font-medium';
        }
    }

    function toggleStatusSheet() {
        const sheet = document.getElementById('status-sheet-backdrop');
        if (sheet.classList.contains('hidden')) {
            sheet.classList.remove('hidden');
            sheet.classList.add('flex');
        } else {
            sheet.classList.add('hidden');
            sheet.classList.remove('flex');
        }
    }

    // Status form submit
    document.getElementById('status-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const status = formData.get('invoice_status');
        
        try {
            const res = await fetch('{{ route('invoices.status.update', $invoice) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status })
            });
            const data = await res.json();
            toggleStatusSheet();
            if (data.success) {
                showToast('Status faktur berhasil diperbarui');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        } catch (err) {
            showToast('Gagal memperbarui status', 'error');
        }
    });

    function triggerPdfDownload() {
        const btnText = document.getElementById('pdf-btn-text');
        const icon = document.getElementById('pdf-icon');
        const prevText = btnText.textContent;
        
        btnText.textContent = 'Merender PDF...';
        icon.textContent = 'hourglass_top';
        
        setTimeout(() => {
            btnText.textContent = prevText;
            icon.textContent = 'download';
            showToast('Dokumen Faktur {{ $invoice->invoice_number }}.pdf siap diunduh');
            window.location.href = '{{ route('invoices.pdf', $invoice) }}';
        }, 900);
    }

    function copyBladeCode() {
        const copyText = document.getElementById('copy-btn-text');
        navigator.clipboard.writeText('<x-components.invoice-paper :invoice="$invoice" />');
        copyText.textContent = 'Tersalin!';
        setTimeout(() => {
            copyText.textContent = 'Salin Snippet';
        }, 2000);
        showToast('Snippet Blade disalin ke clipboard');
    }

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');
        
        toastMsg.textContent = msg;
        toastIcon.textContent = type === 'error' ? 'error' : 'check_circle';
        toastIcon.className = type === 'error' ? 'material-symbols-outlined text-[18px] text-error' : 'material-symbols-outlined text-tertiary-fixed text-[18px]';
        
        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.classList.add('opacity-100');
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'pointer-events-none');
            toast.classList.remove('opacity-100');
        }, 3200);
    }
</script>
@endpush
@endsection