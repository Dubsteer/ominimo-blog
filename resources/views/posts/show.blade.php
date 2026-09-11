@extends('layouts.app')

@section('title', $post->title.' | '.config('app.name'))

@section('content')
    <article class="mx-auto max-w-3xl px-5 py-16">
        @if (session('status') === 'post-created')
            <p class="mb-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Post created.</p>
        @elseif (session('status') === 'post-updated')
            <p class="mb-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Post updated.</p>
        @elseif (session('status') === 'comment-created')
            <p class="mb-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Comment added.</p>
        @elseif (session('status') === 'comment-deleted')
            <p class="mb-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Comment deleted.</p>
        @elseif (session('status') === 'comment-status-updated')
            <p class="mb-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Comment status updated.</p>
        @endif

        <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wide">
            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-900">{{ $post->claim_stage->label() }}</span>
            @if ($post->status === \App\Enums\PostStatus::Draft)
                <span class="rounded-full bg-slate-200 px-3 py-1 text-slate-700">Draft preview</span>
            @endif
        </div>

        <h1 class="mt-5 text-4xl font-bold tracking-tight sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-5 text-slate-500">
            By {{ $post->user->name }}
            <span aria-hidden="true">&middot;</span>
            {{ ($post->published_at ?? $post->created_at)->toFormattedDateString() }}
        </p>

        @if ($post->processed_image_path)
            <img src="{{ $post->processedImageUrl() }}" alt="" class="mt-8 aspect-video w-full rounded-2xl object-cover shadow-sm">
        @elseif ($post->original_image_path)
            <p class="mt-8 rounded-xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-600" role="status">The supporting image is being processed.</p>
        @endif

        @canany(['update', 'delete'], $post)
            <div class="mt-8 flex flex-wrap items-center gap-4 border-y border-slate-200 py-4">
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Edit post</a>
                @endcan

                @can('delete', $post)
                    <details>
                        <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete post</summary>
                        <form method="POST" action="{{ route('posts.destroy', $post) }}" class="mt-3 flex items-center gap-3">
                            @csrf
                            @method('DELETE')
                            <span class="text-sm text-slate-600">This cannot be undone.</span>
                            <button type="submit" class="rounded-full bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Confirm delete</button>
                        </form>
                    </details>
                @endcan
            </div>
        @endcanany

        <div class="mt-10 whitespace-pre-line text-lg leading-8 text-slate-800">{{ $post->content }}</div>

        <section class="mt-14 border-t border-slate-200 pt-10" aria-labelledby="comments-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 id="comments-heading" class="text-2xl font-bold">Comments</h2>
                <p class="text-sm font-medium text-slate-500">{{ $comments->count() }} visible</p>
            </div>

            <div class="mt-7 space-y-5">
                @forelse ($comments as $comment)
                    <article id="comment-{{ $comment->id }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-semibold text-slate-900">
                                {{ $comment->user?->name ?? 'Guest' }}
                                <span class="ml-2 text-sm font-normal text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </p>

                            @if ($comment->status === \App\Enums\CommentStatus::Hidden)
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-700">Hidden</span>
                            @endif
                        </div>

                        <p class="mt-3 whitespace-pre-line leading-7 text-slate-700">{{ $comment->comment }}</p>

                        @canany(['delete', 'moderate'], $comment)
                            <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4">
                                @can('delete', $comment)
                                    <form method="POST" action="{{ route('comments.destroy', $comment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-600">Delete comment</button>
                                    </form>
                                @endcan

                                @can('moderate', $comment)
                                    <form method="POST" action="{{ route('comments.status.update', $comment) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="comment-status-{{ $comment->id }}" class="sr-only">Status for comment by {{ $comment->user?->name ?? 'Guest' }}</label>
                                        <select id="comment-status-{{ $comment->id }}" name="status" class="rounded-lg border-slate-300 py-1.5 text-sm">
                                            @foreach (\App\Enums\CommentStatus::cases() as $status)
                                                <option value="{{ $status->value }}" @selected($comment->status === $status)>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-sm font-semibold text-slate-700 hover:text-slate-950">Update status</button>
                                    </form>
                                @endcan
                            </div>
                        @endcanany
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-slate-600">No comments yet. Start the discussion without including personal or claim details.</p>
                @endforelse
            </div>

            @if ($post->status === \App\Enums\PostStatus::Published)
                <form method="POST" action="{{ route('comments.store', $post) }}" class="mt-10 rounded-2xl bg-slate-100 p-6">
                    @csrf
                    <label for="comment" class="text-lg font-bold text-slate-950">Add a comment</label>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Comments are public. Do not include claim or policy numbers, contact details, or payment information.</p>
                    @guest
                        <p class="mt-2 text-sm font-medium text-amber-800">You are commenting as Guest.</p>
                    @endguest
                    <textarea id="comment" name="comment" rows="5" maxlength="2000" required class="mt-4 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('comment') }}</textarea>
                    @error('comment')
                        <p class="mt-2 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="mt-4 rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Post comment</button>
                </form>
            @else
                <p class="mt-8 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Publish this draft before opening it for comments.</p>
            @endif
        </section>
    </article>
@endsection
