@props(['stage'])

@php
    $stageClasses = match ($stage->value) {
        'reporting' => 'bg-blue-50 text-blue-800 ring-blue-200 before:bg-blue-500',
        'assessment' => 'bg-violet-50 text-violet-800 ring-violet-200 before:bg-violet-500',
        'review' => 'bg-amber-50 text-amber-900 ring-amber-200 before:bg-amber-500',
        'decision' => 'bg-orange-50 text-orange-900 ring-orange-200 before:bg-orange-500',
        'payment' => 'bg-emerald-50 text-emerald-800 ring-emerald-200 before:bg-emerald-500',
        'closed' => 'bg-slate-100 text-slate-700 ring-slate-200 before:bg-slate-500',
        default => 'bg-brand-50 text-brand-800 ring-brand-200 before:bg-brand-500',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.08em] ring-1 ring-inset before:size-1.5 before:rounded-full '.$stageClasses]) }}>
    {{ $stage->label() }}
</span>
