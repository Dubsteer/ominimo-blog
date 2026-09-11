@extends('layouts.app')

@section('title', $post->title.' | '.config('app.name'))

@section('content')
    @php($editPostUrl = route('posts.edit', $post))
    <article class='page-shell page-section'>
        <div class='mx-auto max-w-6xl'>
            @if (session('status') === 'post-created')
                <x-notice class='mb-8' role='status'>Post created.</x-notice>
            @elseif (session('status') === 'post-updated')
                <x-notice class='mb-8' role='status'>Post updated.</x-notice>
            @elseif (session('status') === 'comment-created')
                <x-notice class='mb-8' role='status'>Comment added.</x-notice>
            @elseif (session('status') === 'comment-deleted')
                <x-notice class='mb-8' role='status'>Comment deleted.</x-notice>
            @elseif (session('status') === 'comment-status-updated')
                <x-notice class='mb-8' role='status'>Comment status updated.</x-notice>
            @endif

            <a href='{{ route('posts.index') }}' class='inline-flex items-center gap-2 text-sm font-bold text-brand-700 hover:text-brand-900'>
                <span aria-hidden='true'>←</span> Back to stories
            </a>

            <header class='mt-7 overflow-hidden rounded-[2rem] bg-brand-950 text-white shadow-soft'>
                <div class='relative px-6 py-10 sm:px-10 sm:py-14 lg:px-14'>
                    <span class='pointer-events-none absolute -right-16 -top-28 size-80 rounded-full border-[4rem] border-brand-700/40' aria-hidden='true'></span>
                    <div class='relative max-w-4xl'>
                        <div class='flex flex-wrap items-center gap-2'>
                            <x-claim-stage :stage='$post->claim_stage' class='!bg-white/10 !text-white !ring-white/20 before:!bg-sun' />
                            @if ($post->status === \App\Enums\PostStatus::Draft)
                                <x-badge class='!bg-sun !text-ink-950 !ring-sun'>Draft preview</x-badge>
                            @else
                                <x-badge class='!bg-white/10 !text-white !ring-white/20'>Published</x-badge>
                            @endif
                        </div>

                        <h1 class='mt-6 text-4xl font-extrabold leading-[1.02] tracking-[-0.055em] sm:text-6xl'>{{ $post->title }}</h1>
                        <p class='mt-6 text-base text-brand-100'>
                            By <strong class='text-white'>{{ $post->user->name }}</strong>
                            <span class='mx-2 text-brand-300' aria-hidden='true'>•</span>
                            {{ ($post->published_at ?? $post->created_at)->toFormattedDateString() }}
                        </p>
                    </div>
                </div>
            </header>

            @if ($post->processed_image_path)
                <img src='{{ $post->processedImageUrl() }}' alt='' class='relative mx-auto -mt-5 aspect-video w-[calc(100%-2rem)] max-w-5xl rounded-[1.75rem] border-4 border-canvas object-cover shadow-soft sm:-mt-8'>
            @elseif ($post->original_image_path)
                <x-notice tone='info' class='mx-auto mt-7 max-w-4xl' role='status'>The supporting image is being processed.</x-notice>
            @endif

            <div class='mx-auto mt-10 grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_17rem] lg:items-start'>
                <div>
                    @canany(['update', 'delete'], $post)
                        <div class='mb-8 flex flex-wrap items-center gap-3 rounded-2xl border border-brand-100 bg-white p-4 shadow-card'>
                            @can('update', $post)
                                <x-button :href='$editPostUrl' size='sm'>Edit post</x-button>
                            @endcan

                            @can('delete', $post)
                                <details class='group'>
                                    <summary class='min-h-10 cursor-pointer list-none rounded-full px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-50'>Delete post</summary>
                                    <form method='POST' action='{{ route('posts.destroy', $post) }}' class='mt-3 flex flex-wrap items-center gap-3 rounded-xl bg-red-50 p-3'>
                                        @csrf
                                        @method('DELETE')
                                        <span class='text-sm text-red-900'>This cannot be undone.</span>
                                        <x-button type='submit' variant='danger' size='sm'>Confirm delete</x-button>
                                    </form>
                                </details>
                            @endcan
                        </div>
                    @endcanany

                    <div class='surface-card px-6 py-8 sm:px-9 sm:py-10'>
                        <div class='whitespace-pre-line text-lg leading-8 text-ink-800'>{{ $post->content }}</div>
                    </div>
                </div>

                <aside class='space-y-5 lg:sticky lg:top-28'>
                    <div class='rounded-3xl bg-sun-light p-6'>
                        <span class='grid size-11 place-items-center rounded-2xl bg-sun text-lg font-black text-ink-950' aria-hidden='true'>!</span>
                        <h2 class='mt-5 text-lg font-extrabold'>Protect your privacy</h2>
                        <p class='mt-2 text-sm leading-6 text-ink-600'>Do not share claim or policy numbers, contact details, payment data, or medical information in comments.</p>
                    </div>
                    <div class='surface-card p-6'>
                        <p class='text-xs font-extrabold uppercase tracking-[0.1em] text-ink-600'>Story details</p>
                        <dl class='mt-4 space-y-4 text-sm'>
                            <div><dt class='font-semibold text-ink-600'>Claim stage</dt><dd class='mt-1 font-extrabold text-ink-950'>{{ $post->claim_stage->label() }}</dd></div>
                            <div class='border-t border-brand-100 pt-4'><dt class='font-semibold text-ink-600'>Status</dt><dd class='mt-1 font-extrabold text-ink-950'>{{ $post->status->label() }}</dd></div>
                            <div class='border-t border-brand-100 pt-4'><dt class='font-semibold text-ink-600'>Discussion</dt><dd class='mt-1 font-extrabold text-ink-950'>{{ $comments->count() }} visible {{ \Illuminate\Support\Str::plural('comment', $comments->count()) }}</dd></div>
                        </dl>
                    </div>
                </aside>
            </div>

            <section class='mx-auto mt-16 max-w-5xl border-t border-brand-200 pt-10' aria-labelledby='comments-heading'>
                <div class='flex flex-wrap items-end justify-between gap-4'>
                    <div>
                        <p class='eyebrow'>Community discussion</p>
                        <h2 id='comments-heading' class='section-title mt-4'>Comments</h2>
                    </div>
                    <p class='rounded-full bg-white px-3 py-1.5 text-sm font-bold text-ink-600 shadow-card'>{{ $comments->count() }} visible</p>
                </div>

                <div class='mt-8 space-y-5'>
                    @forelse ($comments as $comment)
                        <article id='comment-{{ $comment->id }}' class='surface-card p-5 sm:p-6'>
                            <div class='flex flex-wrap items-start justify-between gap-3'>
                                <div class='flex items-center gap-3'>
                                    <span class='grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 font-extrabold text-brand-700' aria-hidden='true'>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($comment->user?->name ?? 'Guest', 0, 1)) }}</span>
                                    <p>
                                        <strong class='block text-ink-950'>{{ $comment->user?->name ?? 'Guest' }}</strong>
                                        <span class='text-sm text-ink-600'>{{ $comment->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>

                                @if ($comment->status === \App\Enums\CommentStatus::Hidden)
                                    <x-badge tone='neutral'>Hidden</x-badge>
                                @else
                                    <x-badge tone='success'>Active</x-badge>
                                @endif
                            </div>

                            <p class='mt-5 whitespace-pre-line leading-7 text-ink-800'>{{ $comment->comment }}</p>

                            @canany(['delete', 'moderate'], $comment)
                                <div class='mt-5 flex flex-wrap items-center gap-4 border-t border-brand-100 pt-4'>
                                    @can('delete', $comment)
                                        <form method='POST' action='{{ route('comments.destroy', $comment) }}'>
                                            @csrf
                                            @method('DELETE')
                                            <button type='submit' class='min-h-10 rounded-full px-3 text-sm font-bold text-red-700 hover:bg-red-50'>Delete comment</button>
                                        </form>
                                    @endcan

                                    @can('moderate', $comment)
                                        <form method='POST' action='{{ route('comments.status.update', $comment) }}' class='flex flex-wrap items-center gap-2'>
                                            @csrf
                                            @method('PATCH')
                                            <label for='comment-status-{{ $comment->id }}' class='sr-only'>Status for comment by {{ $comment->user?->name ?? 'Guest' }}</label>
                                            <select id='comment-status-{{ $comment->id }}' name='status' class='rounded-xl border-brand-200 py-2 text-sm focus:border-brand-500 focus:ring-brand-200'>
                                                @foreach (\App\Enums\CommentStatus::cases() as $status)
                                                    <option value='{{ $status->value }}' @selected($comment->status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button type='submit' class='min-h-10 rounded-full px-3 text-sm font-bold text-brand-700 hover:bg-brand-50'>Update status</button>
                                        </form>
                                    @endcan
                                </div>
                            @endcanany
                        </article>
                    @empty
                        <x-empty-state title='No comments yet'>
                            Start the discussion without including personal or claim details.
                        </x-empty-state>
                    @endforelse
                </div>

                @if ($post->status === \App\Enums\PostStatus::Published)
                    <form method='POST' action='{{ route('comments.store', $post) }}' class='mt-10 rounded-[1.75rem] bg-brand-600 p-6 text-white shadow-soft sm:p-8'>
                        @csrf
                        <label for='comment' class='text-2xl font-extrabold tracking-tight'>Add a comment</label>
                        <p id='comment-help' class='mt-2 max-w-2xl text-sm leading-6 text-brand-50'>Comments are public. Focus on helpful questions and experiences, without claim or policy numbers, contact details, or payment information.</p>
                        @guest
                            <p class='mt-3 inline-flex rounded-full bg-white/10 px-3 py-1.5 text-sm font-bold text-white'>You are commenting as Guest.</p>
                        @endguest
                        <textarea id='comment' name='comment' rows='5' maxlength='2000' required aria-describedby='comment-help @error('comment') comment-error @enderror' @error('comment') aria-invalid='true' @enderror class='mt-5 block w-full rounded-2xl border-0 bg-white px-4 py-3 text-ink-950 shadow-sm focus:ring-4 focus:ring-sun'>{{ old('comment') }}</textarea>
                        @error('comment')
                            <p id='comment-error' class='mt-2 text-sm font-bold text-white' role='alert'>{{ $message }}</p>
                        @enderror
                        <x-button type='submit' size='lg' class='mt-5 !bg-sun !text-ink-950 hover:!bg-white hover:!shadow-none'>Post comment</x-button>
                    </form>
                @else
                    <x-notice tone='info' class='mt-8'>Publish this draft before opening it for comments.</x-notice>
                @endif
            </section>
        </div>
    </article>
@endsection
