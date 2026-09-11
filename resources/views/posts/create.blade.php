@extends('layouts.app')

@section('title', 'Create post | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <header>
            <p class='eyebrow'>New discussion</p>
            <h1 class='page-title mt-5'>Share a claim experience.</h1>
            <p class='mt-5 max-w-2xl text-lg leading-8 text-ink-600'>Focus on the process and lessons learned. Save a draft if you still need to remove identifying details.</p>
        </header>

        <div class='mt-10 grid gap-7 lg:grid-cols-[1fr_19rem] lg:items-start'>
            <form method='POST' action='{{ route('posts.store') }}' enctype='multipart/form-data' class='surface-card p-6 sm:p-9'>
                @include('posts._form', [
                    'method' => 'POST',
                    'post' => null,
                    'submitLabel' => 'Create post',
                ])
            </form>

            <aside class='rounded-3xl bg-brand-950 p-6 text-white lg:sticky lg:top-28' aria-labelledby='privacy-check-heading'>
                <span class='grid size-12 place-items-center rounded-2xl bg-sun text-xl font-black text-ink-950' aria-hidden='true'>✓</span>
                <h2 id='privacy-check-heading' class='mt-6 text-xl font-extrabold tracking-tight'>Before you publish</h2>
                <ul class='mt-5 space-y-4 text-sm leading-6 text-brand-50'>
                    <li class='border-b border-white/10 pb-4'>Remove names and contact details.</li>
                    <li class='border-b border-white/10 pb-4'>Remove claim and policy numbers.</li>
                    <li class='border-b border-white/10 pb-4'>Never share payment or medical data.</li>
                    <li>Check images for identifying details.</li>
                </ul>
            </aside>
        </div>
    </section>
@endsection
