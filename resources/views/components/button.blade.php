@props([
    'href' => null,
    'size' => 'md',
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-brand-200 bg-white text-ink-950 hover:border-brand-400 hover:bg-brand-50',
        'quiet' => 'text-brand-700 hover:bg-brand-50 hover:text-brand-900',
        'danger' => 'bg-red-700 text-white hover:bg-red-600',
        default => 'bg-brand-600 text-white shadow-sm hover:-translate-y-0.5 hover:bg-brand-500 hover:shadow-lg hover:shadow-brand-200',
    };

    $sizeClasses = match ($size) {
        'sm' => 'min-h-10 px-4 py-2 text-sm',
        'lg' => 'min-h-14 px-7 py-3.5 text-base',
        default => 'min-h-12 px-5 py-3 text-sm',
    };

    $classes = 'inline-flex items-center justify-center gap-2 rounded-full font-bold transition '.$variantClasses.' '.$sizeClasses;
@endphp

@if ($href)
    <a href='{{ $href }}' {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type='{{ $type }}' {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
