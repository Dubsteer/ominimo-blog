@extends('layouts.app')

@section('title', 'Posts | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-16">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Community stories</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight">Claim experiences and questions</h1>
                <p class="mt-4 max-w-2xl text-slate-600">Published discussions are public. When signed in, your own drafts also appear here.</p>
            </div>

            @auth
                <a href="{{ route('posts.create') }}" class="rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Create post</a>
            @endauth
        </div>

        <form method="GET" action="{{ route('posts.index') }}" class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-label="Filter posts">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-5">
                <div class="md:col-span-2 lg:col-span-2">
                    <label for="q" class="text-sm font-semibold text-slate-800">Search</label>
                    <input id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Search titles and experiences" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="claim-stage" class="text-sm font-semibold text-slate-800">Claim stage</label>
                    <select id="claim-stage" name="claim_stage" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">All stages</option>
                        @foreach ($claimStages as $claimStage)
                            <option value="{{ $claimStage->value }}" @selected(($filters['claim_stage'] ?? null) === $claimStage->value)>{{ $claimStage->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="author" class="text-sm font-semibold text-slate-800">Author</label>
                    <select id="author" name="author" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">All authors</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}" @selected((string) ($filters['author'] ?? '') === (string) $author->id)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="discussion" class="text-sm font-semibold text-slate-800">Discussion</label>
                    <select id="discussion" name="discussion" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Any activity</option>
                        @foreach ($discussionOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['discussion'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @auth
                    <div>
                        <label for="status" class="text-sm font-semibold text-slate-800">Publication status</label>
                        <select id="status" name="status" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">All visible statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                @endauth
            </div>

            @if ($errors->any())
                <p class="mt-4 text-sm font-medium text-red-700" role="alert">Check the filter values and try again.</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button type="submit" class="rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Apply filters</button>
                @if ($hasFilters)
                    <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-950">Clear filters</a>
                @endif
                <p class="ml-auto text-sm font-medium text-slate-500">{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('result', $posts->total()) }}</p>
            </div>
        </form>

        @if (session('status') === 'post-deleted')
            <p class="mt-8 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Post deleted.</p>
        @endif

        @if ($posts->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <h2 class="text-2xl font-bold">{{ $hasFilters ? 'No matching posts' : 'No posts yet' }}</h2>
                <p class="mt-2 text-slate-600">
                    {{ $hasFilters ? 'Try removing one or more filters.' : 'The first privacy-safe experience can start the discussion.' }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @if ($post->processed_image_path)
                            <img src="{{ $post->processedImageUrl() }}" alt="" loading="lazy" class="aspect-video w-full object-cover">
                        @endif

                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wide">
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-900">{{ $post->claim_stage->label() }}</span>
                            @if ($post->status === \App\Enums\PostStatus::Draft)
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-slate-700">Draft</span>
                            @endif
                            </div>

                            <h2 class="mt-5 text-2xl font-bold tracking-tight">
                                <a href="{{ route('posts.show', $post) }}" class="hover:underline hover:decoration-amber-400 hover:decoration-2 hover:underline-offset-4">{{ $post->title }}</a>
                            </h2>
                            <p class="mt-3 flex-1 text-slate-600">{{ \Illuminate\Support\Str::limit($post->content, 170) }}</p>
                            <p class="mt-6 text-sm font-medium text-slate-500">
                                By {{ $post->user->name }}
                                <span aria-hidden="true">&middot;</span>
                                {{ ($post->published_at ?? $post->created_at)->toFormattedDateString() }}
                                <span aria-hidden="true">&middot;</span>
                                {{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </section>
@endsection
