@extends('layouts.app')

@section('title', 'Edit post | '.config('app.name'))

@section('content')
    @php($showPostUrl = route('posts.show', $post))
    <section class='page-shell page-section'>
        <header>
            <div class='flex flex-wrap items-center gap-3'>
                <p class='eyebrow'>Post settings</p>
                @if ($post->status === \App\Enums\PostStatus::Draft)
                    <x-badge tone='neutral'>Draft</x-badge>
                @else
                    <x-badge tone='success'>Published</x-badge>
                @endif
            </div>
            <h1 class='page-title mt-5'>Edit your story.</h1>
            <p class='mt-5 max-w-2xl text-lg leading-8 text-ink-600'>Update the content, stage, visibility, or supporting image. The public URL remains stable.</p>
        </header>

        <div class='mt-10 grid gap-7 lg:grid-cols-[1fr_19rem] lg:items-start'>
            <form method='POST' action='{{ route('posts.update', $post) }}' enctype='multipart/form-data' class='surface-card p-6 sm:p-9'>
                @include('posts._form', [
                    'method' => 'PUT',
                    'submitLabel' => 'Save changes',
                ])
            </form>

            <aside class='rounded-3xl bg-brand-50 p-6 ring-1 ring-brand-100 lg:sticky lg:top-28' aria-labelledby='editing-tip-heading'>
                <span class='grid size-12 place-items-center rounded-2xl bg-brand-600 text-xl font-black text-white' aria-hidden='true'>i</span>
                <h2 id='editing-tip-heading' class='mt-6 text-xl font-extrabold tracking-tight'>A quick review helps</h2>
                <p class='mt-3 text-sm leading-6 text-ink-600'>Read the story once more for details that could identify you, another person, a claim, or a policy.</p>
                <x-button :href='$showPostUrl' variant='secondary' class='mt-6 w-full'>View post</x-button>
            </aside>
        </div>
    </section>
@endsection
