@props(['id', 'title', 'subtitle' => null, 'showClose' => true, 'width' => 'max-w-lg'])

<div class="fixed inset-0 z-50 bg-inverse-surface/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 ease-out" id="{{ $id }}-backdrop" onclick="closeBottomSheet('{{ $id }}')">
    <div class="fixed bottom-0 inset-x-0 {{ $width }} mx-auto bg-surface-container-lowest rounded-t-2xl shadow-2xl p-space-md pb-safe transform translate-y-full transition-transform duration-300 ease-out" id="{{ $id }}-panel" onclick="event.stopPropagation()">
        <!-- Drag Handle Pill -->
        <div class="w-12 h-1 bg-outline-variant/60 rounded-full mx-auto mb-space-md"></div>
        
        <!-- Sheet Header -->
        <div class="flex items-start justify-between mb-space-md">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="font-numeric-md text-numeric-md font-semibold text-on-surface" id="{{ $id }}-title">{{ $title }}</span>
                </div>
                @if($subtitle)
                    <h4 class="font-headline-sm text-headline-sm text-on-surface mt-0.5 font-bold line-clamp-1" id="{{ $id }}-subtitle">{{ $subtitle }}</h4>
                @endif
            </div>
            @if($showClose)
                <button aria-label="Tutup" class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-secondary hover:text-on-surface" onclick="closeBottomSheet('{{ $id }}')">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            @endif
        </div>

        <!-- Sheet Content -->
        <div id="{{ $id }}-content">
            {{ $slot }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openBottomSheet(id) {
        const backdrop = document.getElementById(id + '-backdrop');
        const panel = document.getElementById(id + '-panel');
        backdrop.classList.remove('pointer-events-none', 'opacity-0');
        backdrop.classList.add('opacity-100');
        panel.classList.remove('translate-y-full');
        panel.classList.add('translate-y-0');
        document.body.style.overflow = 'hidden';
    }

    function closeBottomSheet(id) {
        const backdrop = document.getElementById(id + '-backdrop');
        const panel = document.getElementById(id + '-panel');
        backdrop.classList.add('opacity-0');
        backdrop.classList.remove('opacity-100');
        panel.classList.add('translate-y-full');
        panel.classList.remove('translate-y-0');
        setTimeout(() => {
            backdrop.classList.add('pointer-events-none');
            document.body.style.overflow = '';
        }, 300);
    }
</script>
@endpush