@extends('layouts.app')

@section('title', 'Moderation | '.config('app.name'))

@section('content')
    @php($adminUrl = route('admin.index'))

    <section class='page-shell page-section'>
        <header class='flex flex-wrap items-end justify-between gap-6'>
            <div>
                <p class='eyebrow'>Administration</p>
                <h1 class='page-title mt-5'>Moderation workspace</h1>
                <p class='mt-5 max-w-2xl text-lg leading-8 text-ink-600'>Review publication states and keep community discussions useful and privacy-safe.</p>
            </div>
            @if (auth()->user()->isAdministrator())
                <a href='{{ route('admin.users.index') }}' class='inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-brand-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-800'>
                    Manage roles <span aria-hidden='true'>→</span>
                </a>
            @endif
        </header>

        @if (session('status') === 'post-status-updated')
            <x-notice class='mt-7' role='status'>Post publication status updated.</x-notice>
        @elseif (session('status') === 'comment-status-updated')
            <x-notice class='mt-7' role='status'>Comment moderation status updated.</x-notice>
        @endif

        @if ($errors->any())
            <x-notice tone='danger' class='mt-7' role='alert'>{{ $errors->first() }}</x-notice>
        @endif

        <dl class='mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4'>
            <div class='surface-card p-5'>
                <dt class='text-sm font-bold text-ink-600'>All posts</dt>
                <dd class='mt-2 text-3xl font-extrabold tracking-tight'>{{ $stats['posts'] }}</dd>
            </div>
            <div class='surface-card p-5'>
                <dt class='text-sm font-bold text-ink-600'>Draft posts</dt>
                <dd class='mt-2 text-3xl font-extrabold tracking-tight text-amber-700'>{{ $stats['drafts'] }}</dd>
            </div>
            <div class='surface-card p-5'>
                <dt class='text-sm font-bold text-ink-600'>All comments</dt>
                <dd class='mt-2 text-3xl font-extrabold tracking-tight'>{{ $stats['comments'] }}</dd>
            </div>
            <div class='surface-card p-5'>
                <dt class='text-sm font-bold text-ink-600'>Hidden comments</dt>
                <dd class='mt-2 text-3xl font-extrabold tracking-tight text-red-700'>{{ $stats['hiddenComments'] }}</dd>
            </div>
        </dl>

        <nav class='mt-8 flex gap-2 overflow-x-auto rounded-2xl border border-brand-100 bg-white p-2 shadow-card' aria-label='Moderation sections'>
            <a href='#posts' class='shrink-0 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white'>Posts</a>
            <a href='#comments' class='shrink-0 rounded-xl px-5 py-3 text-sm font-bold text-ink-600 hover:bg-brand-50 hover:text-brand-800'>Comments</a>
        </nav>

        <section id='posts' class='scroll-mt-24 pt-12' aria-labelledby='posts-heading'>
            <div class='flex flex-wrap items-end justify-between gap-4'>
                <div>
                    <p class='eyebrow'>Publication review</p>
                    <h2 id='posts-heading' class='section-title mt-4'>Posts</h2>
                </div>
                <p class='text-sm font-bold text-ink-600'>{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('result', $posts->total()) }}</p>
            </div>

            <form method='GET' action='{{ route('admin.index') }}' class='mt-6 rounded-3xl border border-brand-100 bg-brand-50/55 p-5 shadow-card' aria-label='Filter posts for moderation'>
                <div class='grid gap-4 md:grid-cols-2 xl:grid-cols-4'>
                    <div>
                        <label for='post-q' class='form-label'>Search posts</label>
                        <input id='post-q' name='post_q' type='search' maxlength='100' value='{{ $filters['post_q'] ?? '' }}' placeholder='Title or content' class='form-control'>
                    </div>
                    <div>
                        <label for='post-status' class='form-label'>Status</label>
                        <select id='post-status' name='post_status' class='form-control'>
                            <option value=''>All statuses</option>
                            @foreach ($postStatuses as $status)
                                <option value='{{ $status->value }}' @selected(($filters['post_status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for='post-stage' class='form-label'>Claim stage</label>
                        <select id='post-stage' name='post_claim_stage' class='form-control'>
                            <option value=''>All stages</option>
                            @foreach ($claimStages as $stage)
                                <option value='{{ $stage->value }}' @selected(($filters['post_claim_stage'] ?? null) === $stage->value)>{{ $stage->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for='post-author' class='form-label'>Author</label>
                        <select id='post-author' name='post_author' class='form-control'>
                            <option value=''>All authors</option>
                            @foreach ($postAuthors as $author)
                                <option value='{{ $author->id }}' @selected((string) ($filters['post_author'] ?? '') === (string) $author->id)>{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class='mt-5 flex flex-wrap gap-3'>
                    <x-button type='submit'>Filter posts</x-button>
                    @if ($hasPostFilters)
                        <a href='{{ $adminUrl }}#posts' class='inline-flex min-h-12 items-center rounded-full px-4 text-sm font-bold text-brand-700 hover:bg-white'>Clear post filters</a>
                    @endif
                </div>
            </form>

            <div class='mt-6 overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-card'>
                <div class='overflow-x-auto'>
                    <table class='min-w-full border-collapse text-left'>
                        <thead class='bg-brand-950 text-sm text-white'>
                            <tr>
                                <th scope='col' class='px-5 py-4 font-bold'>Post</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Journey</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Activity</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Publication</th>
                            </tr>
                        </thead>
                        <tbody class='divide-y divide-brand-100'>
                            @forelse ($posts as $post)
                                <tr class='align-top'>
                                    <td class='min-w-72 px-5 py-5'>
                                        <a href='{{ route('posts.show', $post) }}' class='font-extrabold text-ink-950 hover:text-brand-700 hover:underline'>{{ $post->title }}</a>
                                        <p class='mt-1 text-sm text-ink-600'>By {{ $post->user->name }}</p>
                                    </td>
                                    <td class='whitespace-nowrap px-5 py-5'><x-claim-stage :stage='$post->claim_stage' /></td>
                                    <td class='whitespace-nowrap px-5 py-5 text-sm text-ink-600'>
                                        {{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }}<br>
                                        <span class='text-xs'>{{ $post->created_at->toFormattedDateString() }}</span>
                                    </td>
                                    <td class='min-w-56 px-5 py-4'>
                                        <form method='POST' action='{{ route('admin.posts.status.update', $post) }}' class='flex items-center gap-2'>
                                            @csrf
                                            @method('PATCH')
                                            <label for='post-status-{{ $post->id }}' class='sr-only'>Publication status for {{ $post->title }}</label>
                                            <select id='post-status-{{ $post->id }}' name='status' class='rounded-xl border-brand-200 py-2 text-sm focus:border-brand-500 focus:ring-brand-200'>
                                                @foreach ($postStatuses as $status)
                                                    <option value='{{ $status->value }}' @selected($post->status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button type='submit' class='min-h-10 rounded-full px-3 text-sm font-bold text-brand-700 hover:bg-brand-50'>Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan='4' class='px-5 py-12 text-center text-ink-600'>No posts match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($posts->hasPages())
                    <div class='border-t border-brand-100 p-4'>{{ $posts->links() }}</div>
                @endif
            </div>
        </section>

        <section id='comments' class='scroll-mt-24 pt-16' aria-labelledby='comments-heading'>
            <div class='flex flex-wrap items-end justify-between gap-4'>
                <div>
                    <p class='eyebrow'>Discussion review</p>
                    <h2 id='comments-heading' class='section-title mt-4'>Comments</h2>
                </div>
                <p class='text-sm font-bold text-ink-600'>{{ $comments->total() }} {{ \Illuminate\Support\Str::plural('result', $comments->total()) }}</p>
            </div>

            <form method='GET' action='{{ route('admin.index') }}' class='mt-6 rounded-3xl border border-brand-100 bg-brand-50/55 p-5 shadow-card' aria-label='Filter comments for moderation'>
                <div class='grid gap-4 md:grid-cols-2 xl:grid-cols-4'>
                    <div>
                        <label for='comment-q' class='form-label'>Search comments</label>
                        <input id='comment-q' name='comment_q' type='search' maxlength='100' value='{{ $filters['comment_q'] ?? '' }}' placeholder='Comment text' class='form-control'>
                    </div>
                    <div>
                        <label for='comment-status' class='form-label'>Status</label>
                        <select id='comment-status' name='comment_status' class='form-control'>
                            <option value=''>All statuses</option>
                            @foreach ($commentStatuses as $status)
                                <option value='{{ $status->value }}' @selected(($filters['comment_status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for='comment-author' class='form-label'>Member</label>
                        <select id='comment-author' name='comment_author' class='form-control'>
                            <option value=''>All members</option>
                            @foreach ($commentAuthors as $author)
                                <option value='{{ $author->id }}' @selected((string) ($filters['comment_author'] ?? '') === (string) $author->id)>{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for='comment-origin' class='form-label'>Author type</label>
                        <select id='comment-origin' name='comment_origin' class='form-control'>
                            <option value=''>Members and guests</option>
                            <option value='member' @selected(($filters['comment_origin'] ?? null) === 'member')>Members</option>
                            <option value='guest' @selected(($filters['comment_origin'] ?? null) === 'guest')>Guests</option>
                        </select>
                    </div>
                </div>
                <div class='mt-5 flex flex-wrap gap-3'>
                    <x-button type='submit'>Filter comments</x-button>
                    @if ($hasCommentFilters)
                        <a href='{{ $adminUrl }}#comments' class='inline-flex min-h-12 items-center rounded-full px-4 text-sm font-bold text-brand-700 hover:bg-white'>Clear comment filters</a>
                    @endif
                </div>
            </form>

            <div class='mt-6 overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-card'>
                <div class='overflow-x-auto'>
                    <table class='min-w-full border-collapse text-left'>
                        <thead class='bg-brand-950 text-sm text-white'>
                            <tr>
                                <th scope='col' class='px-5 py-4 font-bold'>Comment</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Author</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Post</th>
                                <th scope='col' class='px-5 py-4 font-bold'>Moderation</th>
                            </tr>
                        </thead>
                        <tbody class='divide-y divide-brand-100'>
                            @forelse ($comments as $comment)
                                <tr class='align-top'>
                                    <td class='min-w-80 px-5 py-5 text-sm leading-6 text-ink-800'>{{ \Illuminate\Support\Str::limit($comment->comment, 180) }}</td>
                                    <td class='whitespace-nowrap px-5 py-5 text-sm'>
                                        <span class='font-bold text-ink-950'>{{ $comment->user?->name ?? 'Guest' }}</span><br>
                                        <span class='text-xs text-ink-600'>{{ $comment->created_at->toFormattedDateString() }}</span>
                                    </td>
                                    <td class='min-w-56 px-5 py-5 text-sm'>
                                        <a href='{{ route('posts.show', $comment->post) }}#comment-{{ $comment->id }}' class='font-bold text-brand-700 hover:underline'>{{ $comment->post->title }}</a>
                                    </td>
                                    <td class='min-w-56 px-5 py-4'>
                                        <form method='POST' action='{{ route('admin.comments.status.update', $comment) }}' class='flex items-center gap-2'>
                                            @csrf
                                            @method('PATCH')
                                            <label for='comment-status-{{ $comment->id }}' class='sr-only'>Moderation status for comment {{ $comment->id }}</label>
                                            <select id='comment-status-{{ $comment->id }}' name='status' class='rounded-xl border-brand-200 py-2 text-sm focus:border-brand-500 focus:ring-brand-200'>
                                                @foreach ($commentStatuses as $status)
                                                    <option value='{{ $status->value }}' @selected($comment->status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button type='submit' class='min-h-10 rounded-full px-3 text-sm font-bold text-brand-700 hover:bg-brand-50'>Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan='4' class='px-5 py-12 text-center text-ink-600'>No comments match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($comments->hasPages())
                    <div class='border-t border-brand-100 p-4'>{{ $comments->links() }}</div>
                @endif
            </div>
        </section>
    </section>
@endsection
