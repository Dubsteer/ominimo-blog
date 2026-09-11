@props([
    'actionHref' => null,
    'actionLabel' => null,
    'title',
])

<div {{ $attributes->class(['rounded-[1.75rem] border border-dashed border-brand-300 bg-white px-6 py-12 text-center shadow-card']) }}>
    <span class='mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl font-black text-brand-700' aria-hidden='true'>?</span>
    <h2 class='mt-5 text-2xl font-extrabold tracking-tight text-ink-950'>{{ $title }}</h2>
    <div class='mx-auto mt-2 max-w-lg text-base leading-7 text-ink-600'>{{ $slot }}</div>
    @if ($actionHref && $actionLabel)
        <x-button :href='$actionHref' class='mt-6'>{{ $actionLabel }}</x-button>
    @endif
</div>
