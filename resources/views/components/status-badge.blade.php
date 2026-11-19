@props(['status', 'dueDate' => null])

@php
    $statuses = config('invoice.statuses');
    $label = $statuses[$status] ?? ucfirst($status);
    $isOverdue = $status === 'sent' && $dueDate && \Carbon\Carbon::parse($dueDate)->isPast();
    
    $classes = match($status) {
        'draft' => 'bg-surface-container text-secondary',
        'sent' => $isOverdue ? 'bg-error-container text-error animate-pulse' : 'bg-secondary-container text-on-secondary-fixed',
        'paid' => 'bg-tertiary-fixed text-on-tertiary-fixed font-semibold',
        'cancelled' => 'bg-outline-variant text-on-surface-variant',
        default => 'bg-surface-container text-secondary',
    };
    
    $icon = match($status) {
        'draft' => 'edit_note',
        'sent' => $isOverdue ? 'event_busy' : 'send',
        'paid' => 'verified',
        'cancelled' => 'cancel',
        default => 'help',
    };
@endphp

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded {{ $classes }} font-label-caps text-label-caps">
    @if($isOverdue)
        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
    @else
        <span class="w-1.5 h-1.5 rounded-full bg-current/30"></span>
    @endif
    {{ $label }}
    @if($isOverdue)
        <span class="px-1.5 py-0.2 rounded bg-error/10 text-[10px] font-semibold">Lewat {{ \Carbon\Carbon::parse($dueDate)->diffInDays(now()) }} hari</span>
    @endif
</span>