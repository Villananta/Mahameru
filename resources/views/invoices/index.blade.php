@extends('layouts.app')

@section('content')
<div class="flex flex-col w-full pb-10">
    <!-- Top Stats & Primary Call to Action -->
    <section class="flex flex-col gap-space-md mb-space-lg">
        <div class="flex items-center justify-between gap-space-sm">
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs">
                    <span class="font-headline-sm text-headline-sm text-on-surface">Arsip Invoice</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-surface-container text-on-surface font-label-caps text-label-caps">
                        {{ $invoices->total() }} Invoice
                    </span>
                </div>
                <span class="font-body-sm text-body-sm text-secondary">Kelola arsip faktur, filter status, dan ekspor PDF</span>
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center gap-1.5 px-space-md py-2.5 rounded bg-primary text-on-primary font-headline-sm text-headline-sm active:scale-95 transition-transform shrink-0 min-h-[44px]">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>+ Buat Faktur</span>
            </a>
        </div>

        <!-- KPI Compact Grid -->
        <section aria-label="Ringkasan Performa Faktur" class="grid grid-cols-2 gap-space-sm mb-space-lg">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between min-h-[96px]">
                <div class="flex items-center justify-between">
                    <span class="font-label-caps text-label-caps text-secondary uppercase tracking-wider">Total Tagihan</span>
                    <div class="w-6 h-6 rounded-full bg-surface-container-low flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[14px]">receipt_long</span>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="font-numeric-md text-numeric-md font-semibold text-on-surface tracking-tight leading-none">{{ 'Rp ' . number_format($kpis['total_outstanding'], 0, ',', '.') }}</div>
                    <div class="font-body-sm text-body-sm text-secondary mt-1 flex items-center gap-1">
                        <span class="font-numeric-sm text-numeric-sm font-medium text-on-surface">{{ $invoices->whereIn('status', ['sent', 'overdue'])->count() }}</span> Invoice Aktif
                    </div>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between min-h-[96px] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-error/5 rounded-bl-full pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <span class="font-label-caps text-label-caps text-error uppercase tracking-wider">Belum Lunas</span>
                    <div class="w-6 h-6 rounded-full bg-error-container flex items-center justify-center text-error">
                        <span class="material-symbols-outlined text-[14px]">warning</span>
                    </div>
                </div>
                <div class="mt-2 relative z-10">
                    <div class="font-numeric-md text-numeric-md font-semibold text-error tracking-tight leading-none">{{ 'Rp ' . number_format($kpis['total_outstanding'], 0, ',', '.') }}</div>
                    <div class="font-body-sm text-body-sm text-error mt-1 flex items-center gap-1 font-medium">
                        <span class="font-numeric-sm text-numeric-sm">{{ $kpis['overdue_count'] }}</span> Jatuh Tempo
                    </div>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between min-h-[96px]">
                <div class="flex items-center justify-between">
                    <span class="font-label-caps text-label-caps text-secondary uppercase tracking-wider">Diterima Bulan Ini</span>
                    <div class="w-6 h-6 rounded-full bg-tertiary-fixed/40 flex items-center justify-center text-on-tertiary-container">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="font-numeric-md text-numeric-md font-semibold text-on-surface tracking-tight leading-none">{{ 'Rp ' . number_format($kpis['total_paid'], 0, ',', '.') }}</div>
                    <div class="font-body-sm text-body-sm text-secondary mt-1 flex items-center gap-1">
                        <span class="font-numeric-sm text-numeric-sm font-medium text-on-surface">{{ $invoices->where('status', 'paid')->count() }}</span> Telah Diselesaikan
                    </div>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between min-h-[96px]">
                <div class="flex items-center justify-between">
                    <span class="font-label-caps text-label-caps text-secondary uppercase tracking-wider">Draft Tersimpan</span>
                    <div class="w-6 h-6 rounded-full bg-surface-container-low flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[14px]">edit_note</span>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="font-numeric-md text-numeric-md font-semibold text-on-surface tracking-tight leading-none">{{ $kpis['draft_count'] }} Faktur</div>
                    <div class="font-body-sm text-body-sm text-secondary mt-1 flex items-center gap-1">
                        <span>Menunggu finalisasi</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Search & Filter Bar -->
        <div class="flex flex-col gap-space-sm">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary text-[22px] pointer-events-none">search</span>
                <input class="w-full h-12 pl-11 pr-11 bg-surface-container-lowest rounded-xl font-body-lg text-body-lg text-on-surface placeholder:text-secondary shadow-sm focus:outline-none focus:ring-2 focus:ring-primary transition-shadow" id="search-input" placeholder="Cari no invoice, klien, atau nilai..." type="search" value="{{ request('search') }}"/>
                <button aria-label="Hapus pencarian" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-secondary hover:text-on-surface {{ request('search') ? '' : 'hidden' }}" id="btn-clear-search">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Horizontal Scrollable Filter Chips -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory py-1" id="filter-container">
                <button class="filter-chip snap-start shrink-0 min-h-[44px] px-4 rounded-xl {{ request('status', 'all') === 'all' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-secondary' }} font-headline-sm text-headline-sm flex items-center gap-2 transition-transform active:scale-95 shadow-sm" data-filter="all">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.5 rounded-full {{ request('status', 'all') === 'all' ? 'bg-on-primary/20' : 'bg-surface-container text-on-surface' }} font-numeric-sm text-numeric-sm">{{ $invoices->total() }}</span>
                </button>
                <button class="filter-chip snap-start shrink-0 min-h-[44px] px-3.5 rounded-xl {{ request('status') === 'overdue' ? 'bg-error-container text-error' : 'bg-surface-container-lowest text-error' }} font-headline-sm text-headline-sm flex items-center gap-1.5 transition-transform active:scale-95 shadow-sm hover:bg-error-container/20" data-filter="overdue">
                    <span class="w-2 h-2 rounded-full bg-error"></span>
                    <span>Jatuh Tempo</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-error-container text-on-error-container font-numeric-sm text-numeric-sm font-semibold">{{ $kpis['overdue_count'] }}</span>
                </button>
                <button class="filter-chip snap-start shrink-0 min-h-[44px] px-3.5 rounded-xl {{ request('status') === 'sent' ? 'bg-secondary-container text-on-secondary-fixed' : 'bg-surface-container-lowest text-on-secondary-container' }} font-headline-sm text-headline-sm flex items-center gap-1.5 transition-transform active:scale-95 shadow-sm hover:bg-secondary-container/30" data-filter="sent">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Terkirim</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-fixed font-numeric-sm text-numeric-sm">{{ $invoices->where('status', 'sent')->where('due_date', '>=', now()->toDateString())->count() }}</span>
                </button>
                <button class="filter-chip snap-start shrink-0 min-h-[44px] px-3.5 rounded-xl {{ request('status') === 'paid' ? 'bg-tertiary-fixed text-on-tertiary-fixed' : 'bg-surface-container-lowest text-on-tertiary-container' }} font-headline-sm text-headline-sm flex items-center gap-1.5 transition-transform active:scale-95 shadow-sm hover:bg-tertiary-fixed/30" data-filter="paid">
                    <span class="w-2 h-2 rounded-full bg-on-tertiary-container"></span>
                    <span>Lunas</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-numeric-sm text-numeric-sm font-semibold">{{ $invoices->where('status', 'paid')->count() }}</span>
                </button>
                <button class="filter-chip snap-start shrink-0 min-h-[44px] px-3.5 rounded-xl {{ request('status') === 'draft' ? 'bg-surface-container text-secondary' : 'bg-surface-container-lowest text-secondary' }} font-headline-sm text-headline-sm flex items-center gap-1.5 transition-transform active:scale-95 shadow-sm hover:bg-surface-container" data-filter="draft">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Draft</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-surface-container text-on-surface font-numeric-sm text-numeric-sm">{{ $kpis['draft_count'] }}</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Industrial Advisory Banner -->
    <div class="flex items-start gap-space-sm p-space-md rounded bg-surface-container-low mb-space-lg">
        <span class="material-symbols-outlined text-secondary text-[20px] shrink-0 mt-0.5">verified_user</span>
        <div class="flex flex-col">
            <span class="font-headline-sm text-headline-sm text-on-surface">Integritas Arsip Pembukuan</span>
            <p class="font-body-sm text-body-sm text-secondary">
                Faktur lama dan nomor seri pajak klien tersimpan permanen di cloud Mahameru Supplies bahkan jika kontak dinonaktifkan.
            </p>
        </div>
    </div>

    <!-- Section Header -->
    <div class="flex items-center justify-between mb-space-sm">
        <div class="flex items-center gap-2">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Daftar Faktur</h2>
            <span class="font-numeric-sm text-numeric-sm text-secondary bg-surface-container-low px-2 py-0.5 rounded-full">{{ $invoices->count() }} ditampilkan dari {{ $invoices->total() }}</span>
        </div>
        <div class="flex items-center gap-1 text-secondary font-label-caps text-label-caps">
            <span>Terbaru</span>
            <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
        </div>
    </div>

    <!-- Stacked Invoice Cards List -->
    <div class="flex flex-col gap-space-md" id="invoice-card-list">
        @forelse($invoices as $invoice)
            @php
                $isOverdue = $invoice->status === 'sent' && $invoice->due_date && $invoice->due_date->isPast();
                $borderColor = match($invoice->status) {
                    'draft' => 'bg-outline-variant',
                    'sent' => $isOverdue ? 'bg-error' : 'bg-secondary',
                    'paid' => 'bg-on-tertiary-container',
                    'cancelled' => 'bg-outline-variant',
                    default => 'bg-outline-variant',
                };
                $statusLabel = match($invoice->status) {
                    'draft' => 'Draft',
                    'sent' => $isOverdue ? 'Jatuh Tempo' : 'Terkirim',
                    'paid' => 'Lunas',
                    'cancelled' => 'Dibatalkan',
                    default => ucfirst($invoice->status),
                };
                $statusBadgeClass = match($invoice->status) {
                    'draft' => 'bg-surface-container text-secondary',
                    'sent' => $isOverdue ? 'bg-error-container text-error' : 'bg-secondary-container text-on-secondary-fixed',
                    'paid' => 'bg-tertiary-fixed text-on-tertiary-fixed font-semibold',
                    'cancelled' => 'bg-outline-variant text-on-surface-variant',
                    default => 'bg-surface-container text-secondary',
                };
                $statusIcon = match($invoice->status) {
                    'draft' => 'edit_calendar',
                    'sent' => $isOverdue ? 'event_busy' : 'schedule',
                    'paid' => 'verified',
                    'cancelled' => 'cancel',
                    default => 'help',
                };
                $format = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
            @endphp

            <article class="invoice-card relative bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col gap-space-sm active:scale-[0.99] transition-transform overflow-hidden" 
                     data-invoice-id="{{ $invoice->id }}"
                     data-invoice-number="{{ $invoice->invoice_number }}"
                     data-client="{{ $invoice->client->name ?? 'N/A' }}"
                     data-amount="{{ $invoice->grand_total }}"
                     data-status="{{ $invoice->status }}"
                     data-due-date="{{ $invoice->due_date?->format('Y-m-d') ?? '' }}"
                     data-is-overdue="{{ $isOverdue ? 'true' : 'false' }}">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $borderColor }}"></div>
                <div class="flex items-start justify-between gap-2 pl-2">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-numeric-md text-numeric-md font-semibold text-on-surface tracking-tight">{{ $invoice->invoice_number }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded {{ $statusBadgeClass }} font-label-caps text-label-caps">
                                <span class="w-1.5 h-1.5 rounded-full bg-current/30"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface mt-1 font-semibold line-clamp-1">{{ $invoice->client->name ?? 'Klien dihapus' }}</h3>
                    </div>
                    <button aria-label="Menu Aksi Faktur {{ $invoice->invoice_number }}" class="w-11 h-11 -mr-2 -mt-2 rounded-lg flex items-center justify-center text-secondary hover:text-on-surface hover:bg-surface-container-low transition-colors" onclick="openActionSheet({{ $invoice->id }}, '{{ $invoice->invoice_number }}', '{{ $invoice->client->name ?? 'N/A' }}', {{ $invoice->grand_total }}, '{{ $invoice->status }}')">
                        <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>
                </div>
                <div class="pl-2 pt-1 flex items-center justify-between text-body-sm text-secondary">
                    <div class="flex items-center gap-1.5 {{ $isOverdue ? 'text-error font-medium' : '' }}">
                        <span class="material-symbols-outlined text-[16px]">{{ $statusIcon }}</span>
                        <span>{{ $invoice->status === 'paid' ? 'Lunas: ' . $invoice->updated_at->format('d M Y') : ($invoice->status === 'sent' ? 'Tempo: ' . $invoice->due_date->format('d M Y') : ($invoice->status === 'draft' ? 'Dibuat: ' . $invoice->created_at->format('d M Y') : 'Dibatalkan: ' . $invoice->updated_at->format('d M Y'))) }}</span>
                        @if($isOverdue)
                            <span class="px-1.5 py-0.2 rounded bg-error/10 text-[10px] font-semibold">Lewat {{ $invoice->due_date->diffInDays(now()) }} hari</span>
                        @endif
                    </div>
                    @if($invoice->status !== 'draft' && $invoice->status !== 'cancelled')
                        <span class="font-numeric-sm text-numeric-sm text-secondary">{{ $invoice->invoice_number }}</span>
                    @endif
                </div>
                <div class="pl-2 pt-2 flex items-center justify-between border-t border-dashed border-outline-variant/40 mt-1">
                    <span class="font-label-caps text-label-caps text-secondary uppercase">Nominal Akhir</span>
                    <div class="font-numeric-hero-mobile text-numeric-hero-mobile text-on-surface font-semibold">{{ $format($invoice->grand_total) }}</div>
                </div>
            </article>
        @empty
            <div class="flex flex-col items-center justify-center text-center p-space-xl bg-surface-container-lowest rounded-xl shadow-sm mt-space-md" id="empty-state">
                <div class="w-16 h-16 rounded-full bg-surface-container-low flex items-center justify-center text-secondary mb-space-sm">
                    <span class="material-symbols-outlined text-[32px]">manage_search</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Tidak Ada Faktur Ditemukan</h3>
                <p class="font-body-md text-body-md text-secondary mt-1 max-w-xs">
                    Periksa kembali ejaan nomor faktur atau ubah parameter filter status di atas.
                </p>
                <button class="mt-space-md min-h-[44px] px-space-md bg-surface-container-low text-on-surface font-headline-sm text-headline-sm rounded-lg hover:bg-surface-container transition-colors" onclick="resetAllFilters()">
                    Reset Semua Filter
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($invoices->hasPages())
        <div class="mt-space-lg flex justify-center">
            {{ $invoices->links() }}
        </div>
    @endif

    <!-- Developer Blade Reference Box -->
    <div class="mt-space-xl bg-surface-container-low p-space-md rounded-xl shadow-sm flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <span class="font-label-caps text-label-caps text-secondary uppercase tracking-wider">Laravel Blade & Livewire Context</span>
            <span class="font-numeric-sm text-numeric-sm text-on-tertiary-container bg-tertiary-fixed px-1.5 py-0.5 rounded">v2.4-MVP</span>
        </div>
        <div class="font-numeric-sm text-numeric-sm text-on-surface-variant bg-surface-container-lowest p-space-sm rounded font-mono leading-relaxed overflow-x-auto">
            <livewire:invoices.archive-index <br/>
            &nbsp;&nbsp;:kpis="$kpiSummary" <br/>
            &nbsp;&nbsp;:filters="['status', 'date_range']"<br/>
            &nbsp;&nbsp;wire:poll.30s <br/>
            />
        </div>
        <p class="font-body-sm text-body-sm text-secondary">
            Komponen telah dipersiapkan untuk binding reaktif Alpine.js (<code class="font-numeric-sm bg-surface-container px-1 py-0.5 rounded">x-data="invoiceArchive()"</code>) dan polling background status pembayaran otomatis.
        </p>
    </div>
</div>

<!-- Action Bottom Sheet -->
<x-bottom-sheet 
    id="invoice-action-sheet" 
    title="#INV-XXXX" 
    subtitle="PT Klien Contoh"
    width="max-w-lg"
>
    <div id="sheet-content">
        <!-- Content injected by JS -->
    </div>
</x-bottom-sheet>

@push('scripts')
<script>
    // Filter chips
    const filterChips = document.querySelectorAll('.filter-chip');
    const searchInput = document.getElementById('search-input');
    const btnClearSearch = document.getElementById('btn-clear-search');
    const invoiceCards = document.querySelectorAll('.invoice-card');
    const emptyState = document.getElementById('empty-state');
    let activeFilter = '{{ request('status', 'all') }}';

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        btnClearSearch.classList.toggle('hidden', searchTerm === '');
        
        let visibleCount = 0;

        invoiceCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            const invoiceNo = (card.getAttribute('data-invoice-number') || '').toLowerCase();
            const client = (card.getAttribute('data-client') || '').toLowerCase();
            const amount = (card.getAttribute('data-amount') || '').toLowerCase();

            const matchesStatus = (activeFilter === 'all') || (cardStatus === activeFilter);
            const matchesSearch = !searchTerm || invoiceNo.includes(searchTerm) || client.includes(searchTerm) || amount.includes(searchTerm);

            if (matchesStatus && matchesSearch) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }
    }

    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            activeFilter = chip.getAttribute('data-filter');
            const url = new URL(window.location);
            if (activeFilter === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', activeFilter);
            }
            window.location.href = url.toString();
        });
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchInput.debounce);
        searchInput.debounce = setTimeout(() => {
            const url = new URL(window.location);
            const term = searchInput.value.trim();
            if (term) {
                url.searchParams.set('search', term);
            } else {
                url.searchParams.delete('search');
            }
            window.location.href = url.toString();
        }, 300);
    });

    btnClearSearch.addEventListener('click', () => {
        searchInput.value = '';
        const url = new URL(window.location);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    });

    function resetAllFilters() {
        window.location.href = '{{ route('invoices.index') }}';
    }

    // Action Sheet
    function openActionSheet(invoiceId, invoiceNo, clientName, amount, status) {
        const card = document.querySelector(`.invoice-card[data-invoice-id="${invoiceId}"]`);
        const dueDateStr = card?.getAttribute('data-due-date');
        const isOverdue = card?.getAttribute('data-is-overdue') === 'true';
        const isLocked = ['paid', 'cancelled'].includes(status);
        const format = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

        let statusBadgeClass, statusLabel;
        if (status === 'draft') {
            statusBadgeClass = 'bg-surface-container text-secondary';
            statusLabel = 'Draft';
        } else if (status === 'sent') {
            statusBadgeClass = isOverdue ? 'bg-error-container text-error' : 'bg-secondary-container text-on-secondary-fixed';
            statusLabel = isOverdue ? 'Jatuh Tempo' : 'Terkirim';
        } else if (status === 'paid') {
            statusBadgeClass = 'bg-tertiary-fixed text-on-tertiary-fixed font-semibold';
            statusLabel = 'Lunas';
        } else {
            statusBadgeClass = 'bg-outline-variant text-on-surface-variant';
            statusLabel = 'Dibatalkan';
        }

        content.innerHTML = `
            <div class="flex flex-col gap-1">
                <!-- Detail & PDF -->
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="window.location.href='{{ route('invoices.show', ':id') }}'.replace(':id', ${invoiceId}); closeBottomSheet('invoice-action-sheet')">
                    <span class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Lihat Detail & Ekspor PDF</span>
                        <span class="font-body-sm text-body-sm text-secondary">Cetak surat jalan & faktur penjualan</span>
                    </div>
                </button>
                <!-- Mark Paid -->
                ${!isLocked ? `
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="updateStatus(${invoiceId}, 'paid')">
                    <span class="w-9 h-9 rounded-lg bg-tertiary-fixed flex items-center justify-center text-on-tertiary-container shrink-0">
                        <span class="material-symbols-outlined text-[20px]">verified</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Tandai Sebagai Lunas</span>
                        <span class="font-body-sm text-body-sm text-secondary">Catat rekonsiliasi manual atau kas</span>
                    </div>
                </button>
                ` : ''}
                <!-- Resend -->
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="resendInvoice(${invoiceId})">
                    <span class="w-9 h-9 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-fixed shrink-0">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Kirim Tagihan via WhatsApp / Email</span>
                        <span class="font-body-sm text-body-sm text-secondary">Kirim tautan pembayaran instan ke PIC</span>
                    </div>
                </button>
                <!-- Edit -->
                <button class="w-full min-h-[48px] px-space-md rounded-xl ${isLocked ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container-low'} flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="${isLocked ? 'return' : "window.location.href='{{ route('invoices.edit', ':id') }}'.replace(':id', ${invoiceId}); closeBottomSheet('invoice-action-sheet')"}" ${isLocked ? 'disabled' : ''}>
                    <span class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </span>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="font-headline-sm text-headline-sm ${isLocked ? 'text-secondary' : ''}">Ubah Rincian Faktur</span>
                            ${isLocked ? '<span class="material-symbols-outlined text-[16px] text-secondary">lock</span>' : ''}
                        </div>
                        <span class="font-body-sm text-body-sm text-secondary">${isLocked ? 'Faktur yang telah lunas/dibatalkan terkunci dari pengeditan' : 'Koreksi kuantitas atau harga satuan'}</span>
                    </div>
                </button>
                <!-- Delete/Archive -->
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-error-container/30 flex items-center gap-space-md text-error text-left transition-colors mt-1" onclick="deleteInvoice(${invoiceId})">
                    <span class="w-9 h-9 rounded-lg bg-error-container flex items-center justify-center text-error shrink-0">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm text-error">Hapus / Batalkan Faktur</span>
                        <span class="font-body-sm text-body-sm text-error/80">Simpan ke tempat sampah berkas</span>
                    </div>
                </button>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-outline-variant/30">
                <button class="w-full min-h-[48px] bg-surface-container text-on-surface font-headline-sm text-headline-sm rounded-xl hover:bg-surface-container-high transition-colors" onclick="closeBottomSheet('invoice-action-sheet')">
                    Kembali ke Daftar
                </button>
            </div>
        `;

        openBottomSheet('invoice-action-sheet');
    }

    function updateStatus(invoiceId, status) {
        fetch(`{{ route('invoices.status.update', ':id') }}`.replace(':id', invoiceId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status })
        })
        .then(res => res.json())
        .then(data => {
            closeBottomSheet('invoice-action-sheet');
            if (data.success) {
                showToast('Status faktur berhasil diperbarui');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(() => showToast('Gagal memperbarui status', 'error'));
    }

    function resendInvoice(invoiceId) {
        closeBottomSheet('invoice-action-sheet');
        showToast('Pengingat WhatsApp terkirim ke klien');
    }

    function deleteInvoice(invoiceId) {
        if (!confirm('Batalkan faktur ini? Tindakan ini akan mengubah status menjadi Dibatalkan.')) return;
        updateStatus(invoiceId, 'cancelled');
    }

    function showToast(message, type = 'success') {
        const existing = document.getElementById('dynamic-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'dynamic-toast';
        toast.className = `fixed bottom-6 left-1/2 -translate-x-1/2 z-50 ${type === 'error' ? 'bg-error' : 'bg-inverse-surface'} text-${type === 'error' ? 'on-error' : 'inverse-on-surface'} px-4 py-2.5 rounded-lg shadow-xl font-body-sm text-body-sm flex items-center gap-2 transform transition-all`;
        toast.innerHTML = `
            <span class="material-symbols-outlined text-[18px] ${type === 'error' ? 'text-on-error' : 'text-tertiary-fixed'}">${type === 'error' ? 'error' : 'check_circle'}</span>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        setTimeout(() => {
            toast.classList.add('-translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endpush
@endsection