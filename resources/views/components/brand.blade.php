@props(['compact' => false])

<a {{ $attributes->class(['group inline-flex items-center gap-3 font-extrabold tracking-[-0.035em] text-ink-950']) }}>
    <span class='relative grid size-10 shrink-0 place-items-center overflow-hidden rounded-[0.9rem] bg-brand-600 text-white shadow-sm transition group-hover:-rotate-3 group-hover:bg-brand-500' aria-hidden='true'>
        <span class='absolute -right-2 -top-2 size-6 rounded-full bg-sun'></span>
        <span class='relative text-lg leading-none'>O</span>
    </span>
    <span class='{{ $compact ? 'text-base' : 'text-lg' }}'>Ominimo <span class='font-medium text-ink-600'>Hub</span></span>
    <span class='sr-only'>home</span>
</a>
