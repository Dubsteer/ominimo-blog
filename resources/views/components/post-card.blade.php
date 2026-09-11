@props(['post'])

<article {{ $attributes->class(['group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-brand-100 bg-white shadow-card transition duration-200 hover:-translate-y-1 hover:border-brand-300 hover:shadow-soft']) }}>
    <a href='{{ route('posts.show', $post) }}' class='relative block overflow-hidden bg-brand-50'>
        @if ($post->processed_image_path)
            <img src='{{ $post->processedImageUrl() }}' alt='' loading='lazy' class='aspect-[16/9] w-full object-cover transition duration-500 group-hover:scale-[1.025]'>
        @else
            <div class='relative aspect-[16/7] overflow-hidden bg-brand-600'>
                <span class='absolute -right-8 -top-12 size-40 rounded-full border-[2.5rem] border-white/10' aria-hidden='true'></span>
                <span class='absolute bottom-4 left-5 text-sm font-extrabold uppercase tracking-[0.12em] text-brand-100'>Community story</span>
            </div>
        @endif
    </a>

    <div class='flex flex-1 flex-col p-6 sm:p-7'>
        <div class='flex flex-wrap items-center gap-2'>
            <x-claim-stage :stage='$post->claim_stage' />
            @if ($post->status === \App\Enums\PostStatus::Draft)
                <x-badge tone='neutral'>Draft</x-badge>
            @else
                <x-badge tone='success'>Published</x-badge>
            @endif
        </div>

        <h2 class='mt-5 text-2xl font-extrabold leading-tight tracking-[-0.035em] text-ink-950'>
            <a href='{{ route('posts.show', $post) }}' class='decoration-brand-400 decoration-2 underline-offset-4 group-hover:underline'>{{ $post->title }}</a>
        </h2>
        <p class='mt-3 flex-1 text-base leading-7 text-ink-600'>{{ \Illuminate\Support\Str::limit($post->content, 160) }}</p>

        <div class='mt-6 flex items-end justify-between gap-4 border-t border-brand-100 pt-5'>
            <p class='text-sm leading-6 text-ink-600'>
                <span class='font-bold text-ink-800'>{{ $post->user->name }}</span><br>
                {{ ($post->published_at ?? $post->created_at)->toFormattedDateString() }}
            </p>
            <span class='shrink-0 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-extrabold text-brand-800'>
                {{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }}
            </span>
        </div>
    </div>
</article>
