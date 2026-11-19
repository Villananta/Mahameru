@extends('layouts.app')

@php
    $isEdit = isset($invoice);
    $title = $isEdit ? 'Edit Faktur' : 'Buat Faktur Baru';
    $submitText = $isEdit ? 'Perbarui Faktur' : 'Terbitkan Faktur';
    $invoiceNumber = $isEdit ? $invoice->invoice_number : 'AUTO (MS-YYYY/MM/XXXX)';
    $issueDate = $isEdit ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d');
    $dueDate = $isEdit ? $invoice->due_date->format('Y-m-d') : now()->addDays(config('invoice.defaults.payment_terms'))->format('Y-m-d');
    $discountType = $isEdit ? $invoice->discount_type : (old('discount_type', config('invoice.defaults.discount_type')));
    $discountValue = $isEdit ? $invoice->discount_value : (old('discount_value', config('invoice.defaults.discount_value')));
    $taxRate = $isEdit ? $invoice->tax_rate : (old('tax_rate', config('invoice.defaults.tax_rate')));
    $notes = $isEdit ? $invoice->notes : old('notes');
    $clientId = $isEdit ? $invoice->client_id : old('client_id');
    $items = $isEdit ? $invoice->items : [];
    $isLocked = $isEdit && $invoice->is_locked;
@endphp

@section('content')
<div class="flex flex-col w-full relative pb-28">
    <!-- Sticky View Segmented Controller -->
    <div class="sticky top-0 z-20 -mx-gutter md:-mx-gutter-desktop px-gutter md:px-gutter-desktop py-2.5 bg-surface-container-lowest/95 backdrop-blur-md shadow-sm mb-space-md">
        <div class="flex items-center p-1 bg-surface-container-low rounded-lg w-full max-w-md mx-auto">
            <button class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all {{ !$isEdit || request()->segment(2) !== 'edit' ? 'bg-primary text-on-primary shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-form-btn" onclick="switchView('form')">
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
                <span>Isi Form</span>
            </button>
            <button class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all {{ request()->segment(2) === 'edit' ? 'bg-primary text-on-primary shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-preview-btn" onclick="switchView('preview')">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
                <span>Preview A4</span>
                <span class="ml-1 px-1.5 py-0.2 bg-surface-container text-on-surface-variant font-label-caps text-label-caps rounded">Live</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: FORM BUILDER -->
    <div class="flex flex-col gap-space-md transition-opacity duration-200" id="view-form" x-data="invoiceForm()" x-init="init()">
        <form action="{{ $isEdit ? route('invoices.update', $invoice) : route('invoices.store') }}" method="POST" @submit.prevent="validateAndSubmit">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1: Header Meta & Client Selector -->
            <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-on-tertiary-container animate-pulse"></span>
                        <span class="font-headline-sm text-headline-sm text-on-surface">Informasi Faktur</span>
                    </div>
                    <span class="font-label-caps text-label-caps px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant">{{ $isEdit ? 'EDIT DRAFT' : 'DRAFT ELEKTRONIK' }}</span>
                </div>

                <!-- Client Selection -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-label-caps text-label-caps text-secondary uppercase">Entitas Klien / Pembeli</label>
                        <button class="inline-flex items-center gap-1 font-headline-sm text-body-sm text-on-surface hover:text-primary active:scale-95 transition-transform" type="button" @click="openQuickClientModal()">
                            <span class="material-symbols-outlined text-[16px]">add_circle</span>
                            <span>+ Klien Baru</span>
                        </button>
                    </div>
                    <div class="relative">
                        <select name="client_id" class="w-full h-12 pl-3.5 pr-10 rounded-lg bg-surface-container-lowest font-body-lg text-body-lg text-on-surface appearance-none focus:outline-none shadow-sm cursor-pointer" required>
                            <option value="" disabled {{ !$clientId ? 'selected' : '' }}>-- Pilih Klien --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} (NPWP: {{ $client->npwp ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-3 pointer-events-none text-secondary">expand_more</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-secondary px-1" x-show="selectedClient" x-transition>
                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                        <span class="font-body-sm text-body-sm truncate" x-text="selectedClient.address"></span>
                    </div>
                </div>

                <!-- Invoice Number & Dates Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md pt-space-xs">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-caps text-label-caps text-secondary uppercase">Nomor Faktur</label>
                        <div class="relative flex items-center">
                            <input type="text" class="w-full h-12 px-3.5 rounded-lg bg-surface-container-lowest font-numeric-md text-numeric-md text-on-surface focus:outline-none shadow-sm" value="{{ $invoiceNumber }}" {{ !$isEdit ? 'readonly' : '' }}/>
                            <span class="material-symbols-outlined absolute right-3 text-secondary text-[18px]">tag</span>
                        </div>
                        @if(!$isEdit)
                            <p class="font-body-sm text-body-sm text-secondary">Nomor otomatis tergenerate saat diterbitkan</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-caps text-label-caps text-secondary uppercase">Tanggal Terbit</label>
                        <div class="relative flex items-center">
                            <input type="date" name="issue_date" class="w-full h-12 px-3.5 rounded-lg bg-surface-container-lowest font-body-lg text-body-lg text-on-surface focus:outline-none shadow-sm" value="{{ $issueDate }}" required/>
                            <span class="material-symbols-outlined absolute right-3 text-secondary text-[18px]">calendar_today</span>
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Tanggal Jatuh Tempo</label>
                    <div class="relative flex items-center">
                        <input type="date" name="due_date" class="w-full h-12 px-3.5 rounded-lg bg-surface-container-lowest font-body-lg text-body-lg text-on-surface focus:outline-none shadow-sm" value="{{ $dueDate }}" required/>
                        <span class="material-symbols-outlined absolute right-3 text-secondary text-[18px]">calendar_today</span>
                    </div>
                </div>

                <!-- Payment Terms Pills -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Cepat Set Jatuh Tempo</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="tempo-pill flex flex-col items-center justify-center py-2 px-1 rounded-lg bg-surface-container-low text-secondary hover:bg-surface-container transition-all {{ $isEdit && $invoice->due_date->diffInDays($invoice->issue_date) == 7 ? 'bg-primary text-on-primary shadow-sm' : '' }}" @click="setTempo(7)">
                            <span class="font-headline-sm text-headline-sm">+7 Hari</span>
                            <span class="font-label-caps text-numeric-sm opacity-80" x-text="formatDate(addDays(issueDate, 7))"></span>
                        </button>
                        <button type="button" class="tempo-pill flex flex-col items-center justify-center py-2 px-1 rounded-lg {{ $isEdit && $invoice->due_date->diffInDays($invoice->issue_date) == 14 ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-secondary hover:bg-surface-container' }} transition-all" @click="setTempo(14)">
                            <span class="font-headline-sm text-headline-sm">+14 Hari</span>
                            <span class="font-label-caps text-numeric-sm opacity-90" x-text="formatDate(addDays(issueDate, 14))"></span>
                        </button>
                        <button type="button" class="tempo-pill flex flex-col items-center justify-center py-2 px-1 rounded-lg {{ $isEdit && $invoice->due_date->diffInDays($invoice->issue_date) == 30 ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-secondary hover:bg-surface-container' }} transition-all" @click="setTempo(30)">
                            <span class="font-headline-sm text-headline-sm">+30 Hari</span>
                            <span class="font-label-caps text-numeric-sm opacity-80" x-text="formatDate(addDays(issueDate, 30))"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section 2: Line Items -->
            <div class="flex flex-col gap-space-sm">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-headline-sm text-headline-sm text-on-surface">Rincian Muatan & Jasa</span>
                        <span class="w-5 h-5 rounded-full bg-surface-container-high text-on-surface font-numeric-sm text-numeric-sm flex items-center justify-center" x-text="items.length"></span>
                    </div>
                    <span class="font-label-caps text-label-caps text-secondary">MATA UANG: IDR</span>
                </div>

                <template x-for="(item, index) in items" :key="item.id || index">
                    <div class="item-row bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col gap-3 relative transition-all" x-transition>
                        <div class="flex items-start justify-between gap-2">
                            <span class="w-6 h-6 rounded bg-surface-container-low text-secondary font-numeric-sm text-numeric-sm flex items-center justify-center shrink-0 mt-1" x-text="index + 1"></span>
                            <div class="flex-1">
                                <label class="font-label-caps text-label-caps text-secondary mb-1 block uppercase">Nama Produk / Material</label>
                                <textarea name="items[@{{ index }}][description]" class="w-full p-2.5 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface focus:outline-none resize-none leading-snug" rows="2" required x-model="item.description">{{ $isEdit ? $items[$index]->description ?? '' : '' }}</textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="font-label-caps text-label-caps text-secondary uppercase">Jumlah (Qty)</label>
                                <input type="number" step="0.01" name="items[@{{ index }}][quantity]" class="item-qty w-full h-11 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" inputmode="decimal" required @input="recalc()" x-model.number="item.quantity" :value="item.quantity ?? 1">
                            </div>
                            <div>
                                <label class="font-label-caps text-label-caps text-secondary uppercase">Harga Satuan (Rp)</label>
                                <input type="number" step="1" name="items[@{{ index }}][unit_price]" class="item-price w-full h-11 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" inputmode="numeric" required @input="recalc()" x-model.number="item.unit_price" :value="item.unit_price ?? 0">
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <div class="flex flex-col">
                                <span class="font-label-caps text-label-caps text-secondary uppercase">Subtotal Baris</span>
                                <span class="item-subtotal-val font-numeric-md text-numeric-md text-on-surface" x-text="formatIDR(item.quantity * item.unit_price)"></span>
                            </div>
                            <button type="button" aria-label="Hapus Item" class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-lg text-error hover:bg-error-container/50 active:scale-95 transition-transform" @click="removeItem(index)">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0" class="text-center py-8 text-secondary">
                    <span class="material-symbols-outlined text-[32px] block mb-2">add_box</span>
                    Belum ada item. Tambahkan baris pertama.
                </div>

                <!-- Add New Item Row Action -->
                <button type="button" class="w-full min-h-[48px] py-3 rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface font-headline-sm text-headline-sm flex items-center justify-center gap-2 active:scale-[0.99] transition-transform shadow-sm" @click="addItem()">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>+ Tambah Baris Item</span>
                </button>
            </div>

            <!-- Section 3: Financial Calculations -->
            <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <span class="font-headline-sm text-headline-sm text-on-surface">Kalkulasi Tagihan</span>
                    <span class="material-symbols-outlined text-secondary">receipt_long</span>
                </div>

                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between text-body-md font-body-md text-secondary">
                        <span>Subtotal Kotor</span>
                        <span class="font-numeric-md text-numeric-md text-on-surface" x-text="formatIDR(subtotal)"></span>
                    </div>

                    <!-- Discount Row -->
                    <div class="flex flex-col gap-1.5 p-2 rounded-lg bg-surface-container-low">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-on-tertiary-container">local_offer</span>
                                <span class="font-headline-sm text-body-md text-on-surface">Potongan / Diskon</span>
                            </div>
                            <div class="flex items-center bg-surface-container-lowest p-0.5 rounded">
                                <button type="button" class="px-2 py-0.5 rounded text-xs font-semibold @{{ discountType === 'percent' ? 'bg-primary text-on-primary' : 'text-secondary' }}" @click="setDiscountType('percent')">%</button>
                                <button type="button" class="px-2 py-0.5 rounded text-xs font-semibold @{{ discountType === 'fixed' ? 'bg-primary text-on-primary' : 'text-secondary' }}" @click="setDiscountType('fixed')">Rp</button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 mt-1">
                            <div class="flex items-center gap-1 w-28">
                                <input type="number" step="0.01" name="discount_value" class="w-full h-8 px-2 rounded bg-surface-container-lowest font-numeric-md text-numeric-md text-on-surface focus:outline-none" @input="recalc()" x-model.number="discountValue" :value="discountValue">
                                <span class="font-numeric-sm text-numeric-sm text-secondary" x-text="discountType === 'percent' ? '%' : 'Rp'"></span>
                            </div>
                            <span class="font-numeric-md text-numeric-md text-on-tertiary-container font-semibold" x-text="formatIDR(discountAmount)"></span>
                        </div>
                        <input type="hidden" name="discount_type" :value="discountType">
                    </div>

                    <!-- PPN Row -->
                    <div class="flex items-center justify-between text-body-md font-body-md text-secondary">
                        <div class="flex items-center gap-1.5">
                            <span>PPN Faktur (11%)</span>
                            <span class="material-symbols-outlined text-[14px] text-secondary">info</span>
                        </div>
                        <span class="font-numeric-md text-numeric-md text-on-surface" x-text="formatIDR(taxAmount)"></span>
                    </div>

                    <!-- Grand Total -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container mt-1">
                        <div class="flex flex-col">
                            <span class="font-label-caps text-label-caps text-secondary uppercase">Total Tagihan Bersih</span>
                            <span class="font-body-sm text-body-sm text-secondary">Sudah termasuk PPN 11%</span>
                        </div>
                        <span class="font-numeric-hero-mobile text-numeric-hero-mobile text-on-surface font-semibold" x-text="formatIDR(grandTotal)"></span>
                    </div>
                </div>

                <!-- Tax Rate Hidden Input -->
                <input type="hidden" name="tax_rate" :value="taxRate">

                <!-- Payment & Bank Info -->
                <div class="flex flex-col gap-2 pt-space-xs">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Instruksi Pembayaran & Rekening</label>
                    <div class="p-3 rounded-lg bg-surface-container-low flex items-start gap-3">
                        <div class="w-10 h-10 rounded bg-primary-container text-on-primary-fixed flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">account_balance</span>
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="font-headline-sm text-headline-sm text-on-surface truncate">{{ $defaults['bank_accounts'][0]['bank_name'] ?? 'BANK CENTRAL ASIA (BCA)' }}</span>
                            <span class="font-numeric-md text-numeric-md text-on-surface font-medium select-all">{{ $defaults['bank_accounts'][0]['account_no'] ?? '883-091-2821' }}</span>
                            <span class="font-body-sm text-body-sm text-secondary truncate">a.n {{ $defaults['bank_accounts'][0]['account_name'] ?? 'PT Mahameru Supplies Nusantara' }}</span>
                        </div>
                    </div>
                    <textarea name="notes" class="w-full p-2.5 rounded-lg bg-surface-container-low font-body-sm text-body-sm text-on-surface focus:outline-none resize-none" placeholder="Catatan opsional untuk pembeli (misal: Harap cantumkan no invoice saat transfer)" rows="2">{{ $notes }}</textarea>
                </div>
            </div>

            <!-- Submit Buttons (Hidden, triggered by floating dock) -->
            <div class="hidden">
                <button type="submit" id="form-submit-btn" class="w-full py-3.5 rounded bg-primary text-on-primary font-headline-sm text-headline-sm flex items-center justify-center gap-2 active:scale-95 transition-transform min-h-[48px]">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    <span x-text="isEdit ? 'Perbarui Faktur' : 'Terbitkan Faktur'"></span>
                </button>
                <button type="button" id="form-draft-btn" class="w-full py-3.5 rounded bg-surface-container-low text-on-surface font-headline-sm text-headline-sm mt-2" @click="saveAsDraft()">
                    <span class="material-symbols-outlined text-[20px]">drafts</span>
                    <span>Simpan Draft</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: A4 PAPER PREVIEW -->
    <div class="hidden flex-col gap-space-md transition-opacity duration-200" id="view-preview">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-1.5 text-secondary">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span class="font-label-caps text-label-caps">LEGAL DOKUMEN CETAK A4</span>
            </div>
            <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface font-headline-sm text-body-sm active:scale-95 transition-transform" onclick="window.print()">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Cetak PDF</span>
            </button>
        </div>

        <!-- Scaled Mobile Paper Frame -->
        <div class="w-full overflow-hidden rounded-xl bg-surface-container-low p-2 sm:p-4 flex justify-center">
            <div class="w-full max-w-[620px] bg-surface-container-lowest text-on-surface rounded-lg shadow-md p-5 sm:p-8 flex flex-col gap-5 text-left">
                @if($isEdit)
                    <x-invoice-paper :invoice="$invoice" mode="preview" />
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-secondary">
                        <span class="material-symbols-outlined text-[48px] mb-3">preview</span>
                        <span class="font-headline-sm text-headline-sm">Pratinjau tersedia setelah item ditambahkan</span>
                        <span class="font-body-sm text-body-sm mt-1">Isi form lalu klik tab "Preview A4"</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- FLOATING BOTTOM CHECKOUT DOCK -->
    <div class="fixed bottom-16 md:bottom-0 inset-x-0 md:left-60 z-30 bg-surface-container-lowest/95 backdrop-blur-xl shadow-[0_-4px_16px_rgba(0,0,0,0.06)] px-gutter md:px-gutter-desktop py-3">
        <div class="flex items-center justify-between gap-3 max-w-4xl mx-auto">
            <div class="flex flex-col leading-none">
                <span class="font-label-caps text-label-caps text-secondary uppercase mb-0.5">Grand Total Tagihan</span>
                <span class="font-numeric-md text-numeric-md font-bold text-on-surface" x-text="formatIDR(grandTotal)"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="min-h-[44px] px-3.5 py-2 rounded-lg bg-surface-container-low text-on-surface font-headline-sm text-body-sm hover:bg-surface-container active:scale-95 transition-all" @click="saveAsDraft()">
                    Draft
                </button>
                <button type="button" class="min-h-[44px] px-4 py-2.5 rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm flex items-center gap-1.5 shadow-md active:scale-95 transition-all" @click="submitForm()">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    <span>Terbitkan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK ADD CLIENT BOTTOM SHEET MODAL -->
    <x-bottom-sheet id="quick-client-sheet" title="Registrasi Klien Kilat" subtitle="Isi data minimal untuk faktur ini" width="max-w-lg">
        <form @submit.prevent="submitQuickClient">
            <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Nama Instansi / Perusahaan</label>
                    <input type="text" name="name" class="h-12 px-3 rounded-lg bg-surface-container-low font-body-lg text-body-lg text-on-surface focus:outline-none" placeholder="Contoh: PT Jaya Mandiri Perkasa" required x-model="quickClient.name">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">NPWP Perusahaan</label>
                    <input type="text" name="npwp" class="h-12 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" placeholder="00.000.000.0-000.000" x-model="quickClient.npwp">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">No. WhatsApp Finance</label>
                    <input type="tel" name="phone" class="h-12 px-3 rounded-lg bg-surface-container-low font-numeric-md text-numeric-md text-on-surface focus:outline-none" placeholder="0812-xxxx-xxxx" x-model="quickClient.phone">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Email Penagihan</label>
                    <input type="email" name="email" class="h-12 px-3 rounded-lg bg-surface-container-low font-body-lg text-body-lg text-on-surface focus:outline-none" placeholder="billing@perusahaan.co.id" x-model="quickClient.email">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-caps text-label-caps text-secondary uppercase">Alamat Lengkap</label>
                    <textarea name="address" class="h-20 px-3 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface focus:outline-none resize-none" placeholder="Kawasan Industri Jababeka Tahap II Blok J No. 4, Cikarang" x-model="quickClient.address"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" class="min-h-[44px] px-4 rounded-lg bg-surface-container-low text-secondary font-headline-sm text-body-sm" @click="closeBottomSheet('quick-client-sheet')">Batal</button>
                <button type="submit" class="min-h-[44px] px-5 rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm shadow">Simpan & Pakai</button>
            </div>
        </form>
    </x-bottom-sheet>

    <!-- Toast -->
    <div class="fixed top-20 inset-x-4 mx-auto max-w-sm z-50 bg-primary text-on-primary rounded-xl px-4 py-3 shadow-lg flex items-center gap-3 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300" id="toast-message">
        <span class="material-symbols-outlined text-[20px] text-on-tertiary-container" id="toast-icon">check_circle</span>
        <span class="font-body-md text-body-md flex-1 text-on-primary" id="toast-text">Berhasil disimpan</span>
    </div>
</div>

@push('scripts')
<script>
    function invoiceForm() {
        return {
            isEdit: {{ $isEdit ? 'true' : 'false' }},
            items: [
                @foreach($items as $index => $item)
                    { id: {{ $item->id ?? 'null' }}, description: {{ json_encode($item->description) }}, quantity: {{ $item->quantity }}, unit_price: {{ $item->unit_price }} },
                @endforeach
            ],
            discountType: '{{ $discountType }}',
            discountValue: {{ $discountValue }},
            taxRate: {{ $taxRate }},
            issueDate: '{{ $issueDate }}',
            dueDate: '{{ $dueDate }}',
            invoiceNumber: '{{ $isEdit ? $invoice->invoice_number : '' }}',
            notes: '{{ $notes ?? '' }}',
            selectedClient: null,
            quickClient: { name: '', npwp: '', phone: '', email: '', address: '' },

            init() {
                this.$watch('items', () => this.recalc(), { deep: true });
                this.$watch('discountType', () => this.recalc());
                this.$watch('discountValue', () => this.recalc());
                this.$watch('issueDate', (val) => this.dueDate = val); // sync due date default
                
                // Initialize selected client
                const clientSelect = document.querySelector('select[name="client_id"]');
                if (clientSelect) {
                    clientSelect.addEventListener('change', (e) => {
                        const selectedId = e.target.value;
                        const client = {{ json_encode($clients->keyBy('id')->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'address' => $c->address, 'npwp' => $c->npwp, 'email' => $c->email, 'phone' => $c->phone])) }};
                        this.selectedClient = client[selectedId] || null;
                    });
                    // Set initial
                    if (clientSelect.value) {
                        const client = {{ json_encode($clients->keyBy('id')->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'address' => $c->address, 'npwp' => $c->npwp, 'email' => $c->email, 'phone' => $c->phone])) }};
                        this.selectedClient = client[clientSelect.value] || null;
                    }
                }
                
                this.recalc();
            },

            addItem() {
                this.items.push({
                    id: null,
                    description: '',
                    quantity: 1,
                    unit_price: 0
                });
            },

            removeItem(index) {
                if (this.items.length <= 1) {
                    this.showToast('Minimal harus ada 1 item transaksi', 'error');
                    return;
                }
                this.items.splice(index, 1);
                this.showToast('Baris item berhasil dihapus');
            },

            setTempo(days) {
                const issue = new Date(this.issueDate);
                const due = new Date(issue);
                due.setDate(issue.getDate() + days);
                this.dueDate = due.toISOString().split('T')[0];
                this.showToast(`Jatuh tempo disetel +${days} hari dari tanggal terbit`);
            },

            setDiscountType(type) {
                this.discountType = type;
                if (type === 'percent') {
                    this.discountValue = 5;
                } else {
                    this.discountValue = 1000000;
                }
                this.recalc();
            },

            recalc() {
                let subtotal = 0;
                this.items.forEach(item => {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    subtotal += qty * price;
                });
                this.subtotal = subtotal;

                let discAmount = 0;
                if (this.discountValue > 0) {
                    if (this.discountType === 'percent') {
                        discAmount = subtotal * (this.discountValue / 100);
                    } else {
                        discAmount = Math.min(this.discountValue, subtotal);
                    }
                }
                this.discountAmount = discAmount;

                const netBeforeTax = Math.max(0, subtotal - discAmount);
                const taxAmount = netBeforeTax * (this.taxRate / 100);
                this.taxAmount = taxAmount;

                this.grandTotal = netBeforeTax + taxAmount;
            },

            formatIDR(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            },

            addDays(dateStr, days) {
                const d = new Date(dateStr);
                d.setDate(d.getDate() + days);
                return d.toISOString().split('T')[0];
            },

            openQuickClientModal() {
                this.quickClient = { name: '', npwp: '', phone: '', email: '', address: '' };
                openBottomSheet('quick-client-sheet');
            },

            async submitQuickClient() {
                if (!this.quickClient.name.trim()) {
                    this.showToast('Mohon isi nama instansi klien', 'error');
                    return;
                }

                try {
                    const res = await fetch('{{ route('clients.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.quickClient)
                    });
                    const data = await res.json();
                    if (data.success) {
                        closeBottomSheet('quick-client-sheet');
                        this.showToast(`Klien "${data.client.name}" siap digunakan`);
                        
                        // Add to select
                        const select = document.querySelector('select[name="client_id"]');
                        const opt = document.createElement('option');
                        opt.value = data.client.id;
                        opt.text = data.client.name + ' (NPWP: ' + (data.client.npwp || '-') + ')';
                        opt.selected = true;
                        select.prepend(opt);
                        select.dispatchEvent(new Event('change'));
                    } else {
                        this.showToast(data.message || 'Gagal menambah klien', 'error');
                    }
                } catch (e) {
                    this.showToast('Error jaringan', 'error');
                }
            },

            saveAsDraft() {
                const form = document.querySelector('#view-form form');
                const submitBtn = document.getElementById('form-draft-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">hourglass_top</span><span>Menyimpan...</span>';
                form.submit();
            },

            submitForm() {
                const form = document.querySelector('#view-form form');
                const submitBtn = document.getElementById('form-submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">hourglass_top</span><span>Menerbitkan...</span>';
                form.submit();
            },

            validateAndSubmit(e) {
                // Basic validation
                const clientId = document.querySelector('select[name="client_id"]').value;
                if (!clientId) {
                    this.showToast('Pilih klien terlebih dahulu', 'error');
                    e.preventDefault();
                    return;
                }
                if (this.items.length === 0) {
                    this.showToast('Minimal 1 item wajib diisi', 'error');
                    e.preventDefault();
                    return;
                }
                let valid = true;
                this.items.forEach((item, i) => {
                    if (!item.description.trim()) {
                        this.showToast(`Deskripsi item #${i+1} kosong`, 'error');
                        valid = false;
                    }
                    if (!item.quantity || item.quantity <= 0) {
                        this.showToast(`Kuantitas item #${i+1} tidak valid`, 'error');
                        valid = false;
                    }
                });
                if (!valid) e.preventDefault();
            },

            showToast(message, type = 'success') {
                const toast = document.getElementById('toast-message');
                const text = document.getElementById('toast-text');
                const icon = document.getElementById('toast-icon');

                text.innerText = message;
                icon.innerText = type === 'error' ? 'error' : 'check_circle';
                icon.className = type === 'error' ? 'material-symbols-outlined text-[20px] text-error' : 'material-symbols-outlined text-[20px] text-on-tertiary-container';

                toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
                toast.classList.add('translate-y-0', 'opacity-100');

                setTimeout(() => {
                    toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
                    toast.classList.remove('translate-y-0', 'opacity-100');
                }, 2500);
            },

            // Helper for preview tab
            createPreviewInvoice() {
                return {
                    invoice_number: this.isEdit ? '@{{ invoiceNumber }}' : 'MS-PREVIEW',
                    client: this.selectedClient ? { name: this.selectedClient.name, address: this.selectedClient.address, npwp: this.selectedClient.npwp, email: this.selectedClient.email } : { name: 'Klien Belum Dipilih', address: '-', npwp: '-', email: '-' },
                    issue_date: { format: (f) => new Date(this.issueDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) },
                    due_date: { format: (f) => new Date(this.dueDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }), isPast: () => new Date(this.dueDate) < new Date() },
                    items: this.items.map(item => ({ description: item.description, quantity: item.quantity, unit_price: item.unit_price, line_total: item.quantity * item.unit_price })),
                    subtotal: this.subtotal,
                    discount_type: this.discountType,
                    discount_value: this.discountValue,
                    discount_amount: this.discountAmount,
                    tax_rate: this.taxRate,
                    tax_amount: this.taxAmount,
                    grand_total: this.grandTotal,
                    notes: '@{{ notes }}',
                    status: 'draft',
                    updated_at: { format: () => new Date().toLocaleDateString('id-ID') }
                };
            }
        }
    }

    function switchView(tab) {
        const formTab = document.getElementById('view-form');
        const previewTab = document.getElementById('view-preview');
        const formBtn = document.getElementById('tab-form-btn');
        const previewBtn = document.getElementById('tab-preview-btn');

        if (tab === 'form') {
            formTab.classList.remove('hidden');
            previewTab.classList.add('hidden');
            formBtn.className = "flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all bg-primary text-on-primary shadow-sm";
            previewBtn.className = "flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all text-secondary hover:text-on-surface";
        } else {
            formTab.classList.add('hidden');
            previewTab.classList.remove('hidden');
            previewBtn.className = "flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all bg-primary text-on-primary shadow-sm";
            formBtn.className = "flex-1 flex items-center justify-center gap-1.5 py-2 rounded-md font-headline-sm text-headline-sm transition-all text-secondary hover:text-on-surface";
        }
    }
</script>
@endpush
@endsection