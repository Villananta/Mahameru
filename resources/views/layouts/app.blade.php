<!DOCTYPE html>
<html class="h-full" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
    <meta content="mobile_tab" name="shell-type"/>
    <title>{{ config('app.name', 'Mahameru Supplies') }} - {{ $title ?? 'Invoice' }}</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Playfair+Display:wght@600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        @layer base {
            html,body { margin:0; padding:0; min-height:100%; }
            body { overscroll-behavior-y:none; }
            .pb-safe { padding-bottom:env(safe-area-inset-bottom,0px); }
            .pt-safe { padding-top:env(safe-area-inset-top,0px); }
            main>:first-child { margin-top:0!important; }
            main>:last-child { margin-bottom:0!important; }
        }
        ::-webkit-scrollbar { display:none; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#f8f9ff", "error-container": "#ffdad6",
                        "on-secondary": "#ffffff", "primary-fixed": "#dce2f7",
                        "on-secondary-fixed-variant": "#3c475a", "surface-variant": "#d3e4fe",
                        "on-background": "#0b1c30", "background": "#f8f9ff",
                        "on-primary-container": "#7d8497", "tertiary-container": "#002115",
                        "secondary-fixed-dim": "#bcc7de", "inverse-surface": "#213145",
                        "primary": "#000000", "surface-dim": "#cbdbf5",
                        "surface": "#f8f9ff", "surface-container": "#e5eeff",
                        "on-tertiary-container": "#339471", "inverse-primary": "#c0c6db",
                        "primary-fixed-dim": "#c0c6db", "error": "#ba1a1a",
                        "on-tertiary-fixed-variant": "#00513a", "surface-container-lowest": "#ffffff",
                        "on-surface": "#0b1c30", "on-primary-fixed-variant": "#404758",
                        "on-secondary-fixed": "#111c2d", "on-secondary-container": "#586377",
                        "surface-container-high": "#dce9ff", "on-error-container": "#93000a",
                        "outline": "#76777d", "tertiary": "#000000",
                        "surface-container-low": "#eff4ff", "outline-variant": "#c6c6cd",
                        "tertiary-fixed-dim": "#7bd8b1", "tertiary-fixed": "#97f5cc",
                        "on-primary-fixed": "#141b2b", "secondary": "#545f73",
                        "on-primary": "#ffffff", "inverse-on-surface": "#eaf1ff",
                        "surface-tint": "#575e70", "primary-container": "#141b2b",
                        "on-tertiary": "#ffffff", "on-error": "#ffffff",
                        "secondary-container": "#d5e0f8", "on-tertiary-fixed": "#002115",
                        "secondary-fixed": "#d8e3fb", "on-surface-variant": "#45464c",
                        "surface-container-highest": "#d3e4fe"
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    spacing: { "gutter-desktop": "1.5rem", "gutter": "1rem", "space-sm": "0.5rem", "margin": "1rem", "space-xs": "0.25rem", "space-md": "1rem", "space-xl": "2.5rem", "space-lg": "1.5rem", "margin-desktop": "2.5rem" },
                    fontFamily: {
                        "display": ["Playfair Display"], "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Playfair Display"], "numeric-sm": ["JetBrains Mono"],
                        "headline-lg": ["Playfair Display"], "label-caps": ["JetBrains Mono"],
                        "body-md": ["Plus Jakarta Sans"], "body-lg": ["Plus Jakarta Sans"],
                        "numeric-md": ["JetBrains Mono"], "body-sm": ["Plus Jakarta Sans"],
                        "numeric-hero-mobile": ["JetBrains Mono"], "display-mobile": ["Playfair Display"],
                        "headline-sm": ["Plus Jakarta Sans"], "numeric-hero": ["JetBrains Mono"]
                    },
                    fontSize: {
                        "display": ["36px", { "lineHeight": "44px", "fontWeight": "600" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "headline-lg-mobile": ["22px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "numeric-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "headline-lg": ["28px", { "lineHeight": "36px", "fontWeight": "600" }],
                        "label-caps": ["11px", { "lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "numeric-md": ["15px", { "lineHeight": "20px", "fontWeight": "500" }],
                        "body-sm": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
                        "numeric-hero-mobile": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.02em", "fontWeight": "600" }],
                        "display-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "600" }],
                        "headline-sm": ["16px", { "lineHeight": "24px", "fontWeight": "600" }],
                        "numeric-hero": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "600" }]
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased min-h-screen flex flex-col md:pl-60">
    <!-- Mobile Top Bar -->
    <header class="fixed top-0 inset-x-0 md:left-60 z-30 bg-surface-container-lowest/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.03)] pt-safe">
        <div class="h-16 px-gutter flex items-center justify-between gap-space-md">
            <div class="flex items-center gap-space-sm overflow-hidden">
                <img alt="Mahameru Supplies Emblem" class="h-8 w-auto object-contain md:hidden shrink-0" src="{{ config('invoice.company.logo_path') }}"/>
                <div class="flex flex-col overflow-hidden">
                    <div class="flex items-center gap-space-xs">
                        <span class="md:hidden font-headline-sm text-headline-sm text-on-surface truncate">Mahameru</span>
                        <span class="hidden md:inline font-headline-md text-headline-md text-on-surface">{{ $topbarTitle ?? 'Invoice' }}</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-surface-container-low text-secondary font-label-caps text-label-caps shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-on-tertiary-container"></span>PROD
                        </span>
                    </div>
                    <span class="md:hidden font-display-mobile text-display-mobile text-on-surface truncate tracking-tight">{{ $topbarSubtitle ?? '' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <button aria-label="Notifikasi Operasional" class="w-11 h-11 flex items-center justify-center rounded-lg hover:bg-surface-container-low text-on-surface-variant">
                    <span class="material-symbols-outlined text-[22px]">notifications</span>
                </button>
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col fixed top-0 left-0 bottom-0 w-60 z-40 bg-surface-container-lowest shadow-[1px_0_12px_rgba(0,0,0,0.03)] px-space-md py-space-lg">
        <div class="flex items-center gap-space-sm mb-space-xl px-space-xs">
            <img alt="Mahameru Supplies Emblem" class="h-8 w-auto object-contain" src="{{ config('invoice.company.logo_path') }}"/>
            <div class="flex flex-col">
                <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none">Mahameru</span>
                <span class="font-label-caps text-label-caps text-secondary uppercase leading-none mt-1">Supplies MVP</span>
            </div>
        </div>
        <nav class="flex flex-col gap-space-xs" data-active-classes="bg-primary text-on-primary font-semibold">
            <a class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-colors {{ request()->routeIs('invoices.index') ? 'bg-primary text-on-primary font-semibold' : '' }}" href="{{ route('invoices.index') }}">
                <span class="material-symbols-outlined text-[20px]">folder_open</span>
                <span class="font-headline-sm text-headline-sm">Arsip</span>
            </a>
            <a class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-colors {{ request()->routeIs('invoices.create') ? 'bg-primary text-on-primary font-semibold' : '' }}" href="{{ route('invoices.create') }}">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                <span class="font-headline-sm text-headline-sm">+ Buat</span>
            </a>
            <a class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-colors {{ request()->routeIs('clients.index') ? 'bg-primary text-on-primary font-semibold' : '' }}" href="{{ route('clients.index') }}">
                <span class="material-symbols-outlined text-[20px]">group</span>
                <span class="font-headline-sm text-headline-sm">Klien</span>
            </a>
        </nav>
        <div class="mt-auto p-space-sm rounded-lg bg-surface-container-low flex items-center justify-between">
            <div class="flex items-center gap-space-sm">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
                <div class="flex flex-col truncate">
                    <span class="font-headline-sm text-headline-sm text-on-surface truncate">Operator POS</span>
                    <span class="font-label-caps text-label-caps text-secondary">Online</span>
                </div>
            </div>
            <span class="w-2 h-2 rounded-full bg-on-tertiary-container"></span>
        </div>
    </aside>

    <!-- Mobile Bottom Nav -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 pb-safe bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_-4px_16px_rgba(0,0,0,0.04)]" data-active-classes="text-primary font-semibold">
        <div class="flex items-center justify-around h-16 px-space-md">
            <a class="flex flex-col items-center justify-center gap-0.5 min-w-[56px] min-h-[44px] text-secondary hover:text-on-surface transition-colors {{ request()->routeIs('invoices.index') ? 'text-primary font-semibold' : '' }}" href="{{ route('invoices.index') }}">
                <span class="material-symbols-outlined text-[24px]">folder</span>
                <span class="font-label-caps text-label-caps">Arsip</span>
            </a>
            <a class="flex items-center justify-center -mt-5 min-w-[48px] min-h-[48px] rounded-full bg-primary text-on-primary shadow-lg active:scale-95 transition-transform {{ request()->routeIs('invoices.create') ? 'ring-2 ring-offset-2 ring-primary' : '' }}" href="{{ route('invoices.create') }}">
                <span class="material-symbols-outlined text-[26px]">add</span>
            </a>
            <a class="flex flex-col items-center justify-center gap-0.5 min-w-[56px] min-h-[44px] text-secondary hover:text-on-surface transition-colors {{ request()->routeIs('clients.index') ? 'text-primary font-semibold' : '' }}" href="{{ route('clients.index') }}">
                <span class="material-symbols-outlined text-[24px]">group</span>
                <span class="font-label-caps text-label-caps">Klien</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col relative w-full pt-16 pb-24 md:pb-gutter-desktop px-gutter md:px-gutter-desktop bg-surface">
        <div class="flex flex-col w-full pb-12">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="fixed top-20 inset-x-4 max-w-sm mx-auto z-50 bg-primary text-on-primary rounded-xl px-4 py-3 shadow-lg flex items-center gap-3 transform translate-y-0 opacity-100 transition-all duration-300" id="flash-success">
                    <span class="material-symbols-outlined text-[20px] text-on-tertiary-container">check_circle</span>
                    <span class="font-body-md text-body-md flex-1 text-on-primary">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="fixed top-20 inset-x-4 max-w-sm mx-auto z-50 bg-error text-on-error rounded-xl px-4 py-3 shadow-lg flex items-center gap-3 transform translate-y-0 opacity-100 transition-all duration-300" id="flash-error">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span class="font-body-md text-body-md flex-1 text-on-error">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>