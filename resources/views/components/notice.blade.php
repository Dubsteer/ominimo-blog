@props(['tone' => 'success'])

@php
    [$classes, $symbol] = match ($tone) {
        'danger' => ['border-red-200 bg-red-50 text-red-900', '!'],
        'info' => ['border-brand-200 bg-brand-50 text-brand-900', 'i'],
        default => ['border-emerald-200 bg-emerald-50 text-emerald-900', '✓'],
    };
@endphp

<div {{ $attributes->class(['flex items-start gap-3 rounded-2xl border px-4 py-3.5 font-semibold '.$classes]) }}>
    <span class='grid size-6 shrink-0 place-items-center rounded-full bg-white/80 text-xs font-black' aria-hidden='true'>{{ $symbol }}</span>
    <div class='pt-0.5'>{{ $slot }}</div>
</div>
