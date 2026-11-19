@extends('layouts.app')

@section('content')
<div class="flex flex-col w-full pb-10">
    <!-- Top Header & Primary CTA -->
    <section class="flex flex-col gap-space-md mb-space-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-space-sm">
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs">
                    <span class="font-headline-sm text-headline-sm text-on-surface">Manajemen Klien</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-surface-container text-on-surface font-label-caps text-label-caps">
                        {{ $clients->total() }} Entitas
                    </span>
                </div>
                <span class="font-body-sm text-body-sm text-secondary">Data master pembeli untuk faktur, riwayat transaksi, & quick-add inline</span>
            </div>
            <button class="inline-flex items-center justify-center gap-1.5 px-space-md py-2.5 rounded bg-primary text-on-primary font-headline-sm text-headline-sm active:scale-95 transition-transform shrink-0 min-h-[44px]" onclick="openQuickAddModal()">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Klien Baru</span>
            </button>
        </div>

        <!-- Live Search -->
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary text-[22px] pointer-events-none">search</span>
            <input type="search" id="client-search" class="w-full h-12 pl-11 pr-11 bg-surface-container-lowest rounded-xl font-body-lg text-body-lg text-on-surface placeholder:text-secondary shadow-sm focus:outline-none focus:ring-2 focus:ring-primary transition-shadow" placeholder="Cari nama, NPWP, email, telepon..." value="{{ request('search') }}"/>
            <button aria-label="Hapus pencarian" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-secondary hover:text-on-surface {{ request('search') ? '' : 'hidden' }}" id="btn-clear-client-search">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </section>

    <!-- Client Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-space-md" id="client-card-grid">
        @forelse($clients as $client)
            @php
                $invoiceCount = $client->invoices_count ?? $client->invoices()->count();
                $totalBilled = $client->invoices()->sum('grand_total');
                $lastInvoice = $client->invoices()->latest('issue_date')->first();
                $statusBadge = $client->is_active 
                    ? 'bg-tertiary-fixed text-on-tertiary-fixed' 
                    : 'bg-surface-container text-on-surface-variant';
                $statusText = $client->is_active ? 'Aktif' : 'Nonaktif';
                $statusIcon = $client->is_active ? 'check_circle' : 'cancel';
            @endphp

            <article class="client-card relative bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col gap-space-sm transition-all hover:shadow-md active:scale-[0.99]"
                     data-client-id="{{ $client->id }}"
                     data-client-name="{{ $client->name }}"
                     data-client-npwp="{{ $client->npwp ?? '' }}"
                     data-client-email="{{ $client->email ?? '' }}"
                     data-client-phone="{{ $client->phone ?? '' }}"
                     data-is-active="{{ $client->is_active }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold line-clamp-1 truncate">{{ $client->name }}</h3>
                        <div class="flex items-center gap-1.5 mt-1 text-secondary font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[16px]">business</span>
                            <span class="truncate">{{ $client->npwp ? 'NPWP: ' . $client->npwp : 'Belum ada NPWP' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg {{ $statusBadge }} font-label-caps text-label-caps">
                            <span class="material-symbols-outlined text-[12px]">{{ $statusIcon }}</span>
                            {{ $statusText }}
                        </span>
                        <button aria-label="Aksi klien {{ $client->name }}" class="w-9 h-9 rounded-lg flex items-center justify-center text-secondary hover:text-on-surface hover:bg-surface-container-low transition-colors" onclick="openClientActionSheet({{ $client->id }})">
                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-1 text-body-sm text-secondary pt-1">
                    @if($client->email)
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="material-symbols-outlined text-[16px] shrink-0">mail</span>
                            <span class="truncate">{{ $client->email }}</span>
                        </div>
                    @endif
                    @if($client->phone)
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="material-symbols-outlined text-[16px] shrink-0">phone</span>
                            <span class="truncate">{{ $client->phone }}</span>
                        </div>
                    @endif
                    @if($client->address)
                        <div class="flex items-start gap-1.5">
                            <span class="material-symbols-outlined text-[16px] shrink-0 mt-0.5">location_on</span>
                            <span class="truncate line-clamp-2">{{ $client->address }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-dashed border-outline-variant/40 mt-auto flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="font-label-caps text-label-caps text-secondary uppercase">Total Tagihan</span>
                        <span class="font-numeric-md text-numeric-md text-on-surface font-semibold">{{ 'Rp ' . number_format($totalBilled, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-body-sm text-secondary">
                        <span>{{ $invoiceCount }} Faktur</span>
                        <span>{{ $lastInvoice ? 'Terakhir: ' . $lastInvoice->issue_date->format('d M Y') : 'Belum ada transaksi' }}</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center text-center p-space-xl bg-surface-container-lowest rounded-xl shadow-sm" id="empty-clients">
                <div class="w-16 h-16 rounded-full bg-surface-container-low flex items-center justify-center text-secondary mb-space-sm">
                    <span class="material-symbols-outlined text-[32px]">group_off</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Belum Ada Data Klien</h3>
                <p class="font-body-md text-body-md text-secondary mt-1 max-w-xs">
                    Tambahkan klien pertama untuk memulai membuat faktur.
                </p>
                <button class="mt-space-md min-h-[44px] px-space-md bg-primary text-on-primary font-headline-sm text-headline-sm rounded-lg hover:bg-primary-container transition-colors" onclick="openQuickAddModal()">
                    + Tambah Klien Pertama
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($clients->hasPages())
        <div class="mt-space-lg flex justify-center">
            {{ $clients->links() }}
        </div>
    @endif

    <!-- Developer Reference -->
    <div class="mt-space-xl bg-surface-container-low p-space-md rounded-xl shadow-sm flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <span class="font-label-caps text-label-caps text-secondary uppercase tracking-wider">Laravel Blade & Livewire Context</span>
            <span class="font-numeric-sm text-numeric-sm text-on-tertiary-container bg-tertiary-fixed px-1.5 py-0.5 rounded">v2.4-MVP</span>
        </div>
        <div class="font-numeric-sm text-numeric-sm text-on-surface-variant bg-surface-container-lowest p-space-sm rounded font-mono leading-relaxed overflow-x-auto">
            <livewire:clients.index <br/>
            &nbsp;&nbsp;:searchable="['name', 'npwp', 'email', 'phone']" <br/>
            &nbsp;&nbsp;wire:poll.60s <br/>
            />
        </div>
        <p class="font-body-sm text-body-sm text-secondary">
            Siap untuk binding reaktif Alpine.js (<code class="font-numeric-sm bg-surface-container px-1 py-0.5 rounded">x-data="clientArchive()"</code>) dan sinkronisasi real-time status aktif/nonaktif.
        </p>
    </div>
</div>

<!-- QUICK ADD CLIENT BOTTOM SHEET -->
<x-bottom-sheet id="quick-add-client-sheet" title="Registrasi Klien Cepat" subtitle="Data minimal untuk terbitkan faktur hari ini" width="max-w-lg">
    <form id="quick-add-form" @submit.prevent="submitQuickClient">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-1">
                <label class="font-label-caps text-label-caps text-secondary uppercase">Nama Instansi / Perusahaan <span class="text-error">*</span></label>
                <input type="text" name="name" class="h-12 px-3 rounded-lg bg-surface-container-low font-body-lg text-body-lg text-on-surface focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Contoh: PT Jaya Mandiri Perkasa" required x-model="quickClient.name">
            </div>
            <div class="flex flex-col gap-1">
                <label class="font-label-caps text-label-caps text-secondary uppercase">NPWP Perusahaan</label>
                <input type="text" name="npwp" class="h-12 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" placeholder="00.000.000.0-000.000" x-model="quickClient.npwp" maxlength="20">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">No. WhatsApp / Telepon</label>
                    <input type="tel" name="phone" class="h-12 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" placeholder="0812-xxxx-xxxx" x-model="quickClient.phone">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Email Penagihan</label>
                    <input type="email" name="email" class="h-12 px-3 rounded-lg bg-surface-container-low font-body-lg text-body-lg text-on-surface focus:outline-none" placeholder="billing@perusahaan.co.id" x-model="quickClient.email">
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="font-label-caps text-label-caps text-secondary uppercase">Alamat Lengkap</label>
                <textarea name="address" class="h-24 px-3 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface focus:outline-none resize-none" placeholder="Kawasan Industri Jababeka Tahap II Blok J No. 4, Cikarang" x-model="quickClient.address"></textarea>
            </div>
            <div class="flex flex-col gap-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" checked x-model="quickClient.is_active">
                    <span class="font-body-sm text-body-sm text-on-surface">Aktifkan klien ini (muncul di dropdown faktur)</span>
                </label>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" class="min-h-[44px] px-4 rounded-lg bg-surface-container-low text-secondary font-headline-sm text-body-sm" onclick="closeBottomSheet('quick-add-client-sheet')">Batal</button>
            <button type="submit" class="min-h-[44px] px-5 rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm shadow" :disabled="submitting">
                <span x-show="!submitting">Simpan & Gunakan</span>
                <span x-show="submitting" class="flex items-center gap-1.5"><span class="material-symbols-outlined animate-spin text-[18px]">hourglass_top</span>Menyimpan...</span>
            </button>
        </div>
    </form>
</x-bottom-sheet>

<!-- CLIENT ACTION SHEET -->
<x-bottom-sheet id="client-action-sheet" title="#CLIENT-NAME" subtitle="Kelola entitas & riwayat transaksi" width="max-w-md">
    <div id="client-sheet-content" class="flex flex-col gap-1">
        <!-- Content injected by JS -->
    </div>
</x-bottom-sheet>

<!-- Toast -->
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-inverse-surface text-inverse-on-surface px-4 py-2.5 rounded-lg shadow-xl font-body-sm text-body-sm flex items-center gap-2 transition-all opacity-0 pointer-events-none" id="client-toast">
    <span class="material-symbols-outlined text-tertiary-fixed text-[18px]" id="client-toast-icon">check_circle</span>
    <span id="client-toast-message">Notifikasi</span>
</div>

@push('scripts')
<script>
    // Search & Filter
    const clientSearch = document.getElementById('client-search');
    const btnClearSearch = document.getElementById('btn-clear-client-search');
    const clientCards = document.querySelectorAll('.client-card');
    const emptyClients = document.getElementById('empty-clients');

    function applyClientSearch() {
        const term = clientSearch.value.toLowerCase().trim();
        btnClearSearch.classList.toggle('hidden', term === '');
        
        let visible = 0;
        clientCards.forEach(card => {
            const name = (card.getAttribute('data-client-name') || '').toLowerCase();
            const npwp = (card.getAttribute('data-client-npwp') || '').toLowerCase();
            const email = (card.getAttribute('data-client-email') || '').toLowerCase();
            const phone = (card.getAttribute('data-client-phone') || '').toLowerCase();
            
            const match = !term || name.includes(term) || npwp.includes(term) || email.includes(term) || phone.includes(term);
            card.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        
        emptyClients.classList.toggle('hidden', visible > 0);
        emptyClients.classList.toggle('flex', visible === 0);
    }

    clientSearch.addEventListener('input', () => {
        clearTimeout(clientSearch.debounce);
        clientSearch.debounce = setTimeout(() => {
            const url = new URL(window.location);
            const term = clientSearch.value.trim();
            if (term) url.searchParams.set('search', term);
            else url.searchParams.delete('search');
            window.location.href = url.toString();
        }, 300);
    });

    btnClearSearch.addEventListener('click', () => {
        clientSearch.value = '';
        const url = new URL(window.location);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    });

    // Quick Add Modal
    function openQuickAddModal() {
        document.querySelector('#quick-add-form').reset();
        document.querySelector('#quick-add-form input[name="is_active"]').checked = true;
        window.quickAddData = { name: '', npwp: '', phone: '', email: '', address: '', is_active: true };
        openBottomSheet('quick-add-client-sheet');
    }

    // Client Action Sheet
    function openClientActionSheet(clientId) {
        const card = document.querySelector(`.client-card[data-client-id="${clientId}"]`);
        if (!card) return;
        
        const name = card.getAttribute('data-client-name');
        const npwp = card.getAttribute('data-client-npwp');
        const email = card.getAttribute('data-client-email');
        const phone = card.getAttribute('data-client-phone');
        const isActive = card.getAttribute('data-is-active') === 'true';
        
        document.getElementById('client-action-sheet-title').textContent = name;
        document.getElementById('client-action-sheet-subtitle').textContent = npwp ? `NPWP: ${npwp}` : 'Belum ada NPWP';
        
        const content = document.getElementById('client-sheet-content');
        content.innerHTML = `
            <div class="flex flex-col gap-1">
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="window.location.href='{{ route('invoices.create') }}?client=${clientId}'; closeBottomSheet('client-action-sheet')">
                    <span class="w-9 h-9 rounded-lg bg-primary-container flex items-center justify-center text-on-primary-fixed shrink-0">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Buat Faktur Baru</span>
                        <span class="font-body-sm text-body-sm text-secondary">Prefill klien ini di form faktur</span>
                    </div>
                </button>
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="viewClientInvoices(${clientId})">
                    <span class="w-9 h-9 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-fixed shrink-0">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Lihat Riwayat Faktur</span>
                        <span class="font-body-sm text-body-sm text-secondary">Semua transaksi klien ini</span>
                    </div>
                </button>
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-surface-container-low flex items-center gap-space-md text-on-surface text-left transition-colors" onclick="editClient(${clientId})">
                    <span class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm">Edit Data Klien</span>
                        <span class="font-body-sm text-body-sm text-secondary">Ubah nama, NPWP, kontak, alamat</span>
                    </div>
                </button>
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-${isActive ? 'error-container' : 'tertiary-fixed'}/30 flex items-center gap-space-md text-${isActive ? 'error' : 'on-tertiary-fixed'} text-left transition-colors" onclick="toggleClientStatus(${clientId}, ${!isActive})">
                    <span class="w-9 h-9 rounded-lg bg-${isActive ? 'error-container' : 'tertiary-fixed'} flex items-center justify-center text-${isActive ? 'error' : 'on-tertiary-fixed'} shrink-0">
                        <span class="material-symbols-outlined text-[20px]">${isActive ? 'block' : 'check_circle'}</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm text-${isActive ? 'error' : 'on-tertiary-fixed'}">${isActive ? 'Nonaktifkan Klien' : 'Aktifkan Klien'}</span>
                        <span class="font-body-sm text-body-sm text-${isActive ? 'error' : 'on-tertiary-fixed'}/80">${isActive ? 'Sembunyikan dari dropdown faktur baru' : 'Tampilkan kembali di dropdown faktur'}</span>
                    </div>
                </button>
                <button class="w-full min-h-[48px] px-space-md rounded-xl hover:bg-error-container/30 flex items-center gap-space-md text-error text-left transition-colors mt-1" onclick="deleteClient(${clientId})">
                    <span class="w-9 h-9 rounded-lg bg-error-container flex items-center justify-center text-error shrink-0">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-headline-sm text-headline-sm text-error">Hapus Permanen</span>
                        <span class="font-body-sm text-body-sm text-error/80">Hapus klien & semua data terkait</span>
                    </div>
                </button>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-outline-variant/30">
                <button class="w-full min-h-[48px] bg-surface-container text-on-surface font-headline-sm text-headline-sm rounded-xl hover:bg-surface-container-high transition-colors" onclick="closeBottomSheet('client-action-sheet')">
                    Tutup
                </button>
            </div>
        `;
        
        openBottomSheet('client-action-sheet');
    }

    function viewClientInvoices(clientId) {
        closeBottomSheet('client-action-sheet');
        window.location.href = '{{ route('invoices.index') }}?client=' + clientId;
    }

    function editClient(clientId) {
        closeBottomSheet('client-action-sheet');
        showToast('Fitur edit klien akan segera hadir (route clients.edit)', 'info');
    }

    async function toggleClientStatus(clientId, activate) {
        if (!confirm(activate ? 'Aktifkan klien ini?' : 'Nonaktifkan klien ini? Faktur baru tidak bisa memilih klien nonaktif.')) return;
        
        try {
            const res = await fetch(`{{ route('clients.update', ':id') }}`.replace(':id', clientId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ is_active: activate })
            });
            const data = await res.json();
            closeBottomSheet('client-action-sheet');
            if (data.success) {
                showToast(activate ? 'Klien diaktifkan' : 'Klien dinonaktifkan');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal mengubah status', 'error');
        }
    }

    function deleteClient(clientId) {
        if (!confirm('HAPUS PERMANEN klien ini? Semua faktur terkait akan ikut terhapus. Tindakan ini TIDAK DAPAT DIBATALKAN.')) return;
        if (!confirm('KONFIRMASI AKHIR: Yakin ingin menghapus klien & semua fakturnya?')) return;
        
        try {
            const res = await fetch(`{{ route('clients.destroy', ':id') }}`.replace(':id', clientId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            closeBottomSheet('client-action-sheet');
            if (data.success) {
                showToast('Klien & data terkait dihapus permanen');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menghapus klien', 'error');
        }
    }

    // Quick Add Form Submit
    document.getElementById('quick-add-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData);
        payload.is_active = formData.has('is_active');
        
        if (!payload.name.trim()) {
            showToast('Nama instansi wajib diisi', 'error');
            return;
        }
        
        submitBtn.disabled = true;
        
        try {
            const res = await fetch('{{ route('clients.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                closeBottomSheet('quick-add-client-sheet');
                showToast(`Klien "${data.client.name}" berhasil ditambahkan`);
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Gagal menambah klien', 'error');
            }
        } catch (err) {
            showToast('Error jaringan', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    });

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('client-toast');
        const msgEl = document.getElementById('client-toast-message');
        const icon = document.getElementById('client-toast-icon');
        
        msgEl.textContent = msg;
        icon.textContent = type === 'error' ? 'error' : (type === 'info' ? 'info' : 'check_circle');
        icon.className = type === 'error' ? 'material-symbols-outlined text-[18px] text-error' : 'material-symbols-outlined text-tertiary-fixed text-[18px]';
        
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