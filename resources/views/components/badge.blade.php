@props(['tone' => 'brand'])

@php
    $toneClasses = match ($tone) {
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-900 ring-amber-200',
        'danger' => 'bg-red-50 text-red-800 ring-red-200',
        default => 'bg-brand-50 text-brand-800 ring-brand-200',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.08em] ring-1 ring-inset '.$toneClasses]) }}>
    {{ $slot }}
</span>
