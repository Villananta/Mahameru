<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Mahameru Supplies - Company Identity (Static Configuration)
    |--------------------------------------------------------------------------
    | Semua data penerbit (issuer) diambil dari sini. Tidak ada tabel
    | `company_profiles` atau serupa di database. Ubah file ini saja
    | saat rebranding atau pindah kantor.
    */

    'company' => [
        'name'          => 'PT Mahameru Supplies Nusantara',
        'short_name'    => 'Mahameru Supplies',
        'tagline'       => 'Industrial Equipment, Steel Structures & Technical Hardware',
        'address'       => 'Kawasan Industri Cikarang Blok B-12, Bekasi, Jawa Barat',
        'phone'         => '(021) 8984-2200',
        'email'         => 'invoice@mahamerusupplies.com',
        'npwp'          => '01.892.411.0-413.000',
        'logo_path'     => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBid4zEgY2oyrnr8JUjAdA4kNV3Umbd9h4V-rqPt5TZMcMlbkPvJZ_CF0XaunN8UQ763FBdlDzCfhpAsDDfAcpIhpYAUDRjXwC8PRl3tFGjbMCXDmPJ6cYGRVgNN0JYcIud5wyhFo4ZpKvnTuPN4N4AQSZ5X3f3uceUpyvcWBpIw0g5GsQ0dhUqQRfBYXnBbAOnJrFZAqOgbT70jvDbuMq9rbMcijU8wE5NoODqMt6h',
    ],

    /*
    |--------------------------------------------------------------------------
    | Banking Details (Static)
    |--------------------------------------------------------------------------
    | Ditulis persis seperti yang akan dicetak di footer faktur.
    */
    'bank_accounts' => [
        [
            'bank_name'     => 'BANK CENTRAL ASIA (BCA)',
            'account_no'    => '883-091-2821',
            'account_name'  => 'PT Mahameru Supplies Nusantara',
            'branch'        => 'KCP Cikarang Techno Park',
            'is_default'    => true,
        ],
        [
            'bank_name'     => 'Bank Mandiri (Persero) Tbk',
            'account_no'    => '137-00-198822-1',
            'account_name'  => 'PT Mahameru Supplies Nusantara',
            'branch'        => 'KC Jakarta Pulogadung',
            'is_default'    => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Numbering Format
    |--------------------------------------------------------------------------
    | Pola: INV/YYYY/MM/XXXX
    | - YYYY  : Tahun terbit (4 digit)
    | - MM    : Bulan terbit (2 digit, leading zero)
    | - XXXX  : Running number per bulan, 4 digit leading zero
    */
    'numbering' => [
        'prefix'        => 'INV',
        'date_format'   => 'Y/m',
        'running_digits'=> 4,
        'separator'     => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tax & Payment Terms
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'tax_rate'          => 11.00, // Persen, default PPN Indonesia
        'payment_terms'     => 14,    // Hari (TOP 14 Hari)
        'discount_type'     => 'percent', // 'fixed' | 'percent'
        'discount_value'    => 0.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Status Definitions
    |--------------------------------------------------------------------------
    | Digunakan untuk validasi transisi status di Service Layer.
    */
    'statuses' => [
        'draft'      => 'Draft',
        'sent'       => 'Terkirim',
        'paid'       => 'Lunas',
        'cancelled'  => 'Dibatalkan',
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF / DomPDF Options
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'paper'   => 'A4',
        'orientation' => 'portrait',
        'options' => [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ],
    ],
];