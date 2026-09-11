@extends('layouts.app')

@section('title', 'Stories | '.config('app.name'))

@section('content')
    @php
        $createPostUrl = route('posts.create');
        $postsIndexUrl = route('posts.index');
    @endphp
    <section class='page-shell page-section'>
        <header class='grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end'>
            <div>
                <p class='eyebrow'>Community stories</p>
                <h1 class='page-title mt-5'>Find a story for your next step.</h1>
                <p class='mt-5 max-w-2xl text-lg leading-8 text-ink-600'>Explore privacy-safe experiences and questions across every stage of the claim journey.</p>
            </div>
            @auth
                <x-button :href='$createPostUrl' size='lg'>Share your experience <span aria-hidden='true'>+</span></x-button>
            @endauth
        </header>

        <div class='mt-9 overflow-hidden rounded-2xl border border-brand-100 bg-white px-5 py-4 shadow-card' aria-label='Claim journey stages'>
            <div class='flex items-center gap-3 overflow-x-auto pb-1'>
                <span class='shrink-0 text-xs font-extrabold uppercase tracking-[0.1em] text-ink-600'>Journey</span>
                @foreach ($claimStages as $claimStage)
                    <a href='{{ route('posts.index', ['claim_stage' => $claimStage->value]) }}' class='shrink-0'>
                        <x-claim-stage :stage='$claimStage' />
                    </a>
                    @unless ($loop->last)
                        <span class='text-brand-300' aria-hidden='true'>→</span>
                    @endunless
                @endforeach
            </div>
        </div>

        <form method='GET' action='{{ route('posts.index') }}' class='mt-8 rounded-[1.75rem] border border-brand-100 bg-brand-50/55 p-5 shadow-card sm:p-7' aria-label='Filter posts'>
            <div class='flex flex-wrap items-center justify-between gap-3'>
                <h2 class='text-lg font-extrabold tracking-tight'>Refine the stories</h2>
                <p class='rounded-full bg-white px-3 py-1.5 text-sm font-bold text-brand-800 shadow-sm'>{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('result', $posts->total()) }}</p>
            </div>

            <div class='mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-12'>
                <div class='md:col-span-2 lg:col-span-4'>
                    <label for='q' class='form-label'>Search</label>
                    <input id='q' name='q' type='search' maxlength='100' value='{{ $filters['q'] ?? '' }}' placeholder='Search titles and experiences' class='form-control'>
                </div>

                <div class='lg:col-span-2'>
                    <label for='claim-stage' class='form-label'>Claim stage</label>
                    <select id='claim-stage' name='claim_stage' class='form-control'>
                        <option value=''>All stages</option>
                        @foreach ($claimStages as $claimStage)
                            <option value='{{ $claimStage->value }}' @selected(($filters['claim_stage'] ?? null) === $claimStage->value)>{{ $claimStage->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class='lg:col-span-2'>
                    <label for='author' class='form-label'>Author</label>
                    <select id='author' name='author' class='form-control'>
                        <option value=''>All authors</option>
                        @foreach ($authors as $author)
                            <option value='{{ $author->id }}' @selected((string) ($filters['author'] ?? '') === (string) $author->id)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class='lg:col-span-2'>
                    <label for='discussion' class='form-label'>Discussion</label>
                    <select id='discussion' name='discussion' class='form-control'>
                        <option value=''>Any activity</option>
                        @foreach ($discussionOptions as $value => $label)
                            <option value='{{ $value }}' @selected(($filters['discussion'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @auth
                    <div class='lg:col-span-2'>
                        <label for='status' class='form-label'>Publication status</label>
                        <select id='status' name='status' class='form-control'>
                            <option value=''>All visible</option>
                            @foreach ($statuses as $status)
                                <option value='{{ $status->value }}' @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                @endauth
            </div>

            @if ($errors->any())
                <x-notice tone='danger' class='mt-5' role='alert'>Check the filter values and try again.</x-notice>
            @endif

            <div class='mt-6 flex flex-wrap items-center gap-3'>
                <x-button type='submit'>Apply filters</x-button>
                @if ($hasFilters)
                    <x-button :href='$postsIndexUrl' variant='quiet'>Clear all</x-button>
                @endif
            </div>
        </form>

        @if (session('status') === 'post-deleted')
            <x-notice class='mt-8' role='status'>Post deleted.</x-notice>
        @endif

        @if ($posts->isEmpty())
            @php
                $emptyTitle = $hasFilters ? 'No matching stories' : 'No stories yet';
                $emptyActionHref = $hasFilters ? route('posts.index') : (auth()->check() ? route('posts.create') : null);
                $emptyActionLabel = $hasFilters ? 'Clear filters' : (auth()->check() ? 'Create the first post' : null);
            @endphp
            <x-empty-state
                class='mt-10'
                :title='$emptyTitle'
                :action-href='$emptyActionHref'
                :action-label='$emptyActionLabel'
            >
                {{ $hasFilters ? 'Try removing one or more filters to widen your search.' : 'The first privacy-safe experience can start a useful discussion.' }}
            </x-empty-state>
        @else
            <div class='mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3'>
                @foreach ($posts as $post)
                    <x-post-card :post='$post' />
                @endforeach
            </div>

            <div class='mt-10 rounded-2xl bg-white p-4 shadow-card'>{{ $posts->links() }}</div>
        @endif
    </section>
@endsection
