@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <div class='relative overflow-hidden rounded-[2rem] bg-brand-950 px-6 py-10 text-white shadow-soft sm:px-10 sm:py-12'>
            <span class='absolute -right-10 -top-20 size-64 rounded-full border-[3rem] border-brand-700/45' aria-hidden='true'></span>
            <div class='relative flex flex-wrap items-end justify-between gap-8'>
                <div>
                    <p class='text-sm font-extrabold uppercase tracking-[0.13em] text-brand-200'>Your dashboard</p>
                    <h1 class='mt-4 text-4xl font-extrabold tracking-[-0.05em] sm:text-5xl'>Hello, {{ auth()->user()->name }}</h1>
                    <p class='mt-4 max-w-2xl text-lg leading-8 text-brand-50'>Pick up a draft, start a new discussion, or update your account.</p>
                </div>
                <x-badge class='!bg-white/10 !text-white !ring-white/20'>{{ auth()->user()->role->label() }} account</x-badge>
            </div>
        </div>

        <div class='mt-8 grid gap-5 md:grid-cols-2'>
            @if (auth()->user()->canModerateContent())
                <a href='{{ route('admin.index') }}' class='group relative overflow-hidden rounded-[1.75rem] bg-brand-600 p-7 text-white shadow-soft transition hover:-translate-y-1 hover:bg-brand-500 sm:p-8 md:col-span-2'>
                    <div class='flex flex-wrap items-center justify-between gap-6'>
                        <div class='flex items-start gap-5'>
                            <span class='grid size-13 shrink-0 place-items-center rounded-2xl bg-white/15 text-xl font-black' aria-hidden='true'>✓</span>
                            <div>
                                <p class='text-sm font-extrabold uppercase tracking-[0.1em] text-brand-100'>Administration</p>
                                <h2 class='mt-2 text-2xl font-extrabold tracking-tight'>Open moderation workspace</h2>
                                <p class='mt-2 max-w-xl leading-7 text-brand-50'>Review post publication and comment visibility from one focused dashboard.</p>
                            </div>
                        </div>
                        <span class='font-bold'>Review content <span aria-hidden='true'>→</span></span>
                    </div>
                </a>
            @endif

            <a href='{{ route('posts.create') }}' class='group surface-card relative overflow-hidden p-7 transition hover:-translate-y-1 hover:border-brand-300 sm:p-8'>
                <div class='flex items-start justify-between gap-5'>
                    <span class='grid size-13 place-items-center rounded-2xl bg-brand-600 text-2xl font-black text-white' aria-hidden='true'>+</span>
                    <span class='text-2xl text-brand-500 transition group-hover:translate-x-1' aria-hidden='true'>→</span>
                </div>
                <h2 class='mt-8 text-2xl font-extrabold tracking-tight'>Create a post</h2>
                <p class='mt-2 max-w-md leading-7 text-ink-600'>Share a question or experience as a private draft or a published discussion.</p>
            </a>

            <a href='{{ route('posts.index') }}' class='group surface-card relative overflow-hidden p-7 transition hover:-translate-y-1 hover:border-brand-300 sm:p-8'>
                <div class='flex items-start justify-between gap-5'>
                    <span class='grid size-13 place-items-center rounded-2xl bg-sun text-xl font-black text-ink-950' aria-hidden='true'>◎</span>
                    <span class='text-2xl text-brand-500 transition group-hover:translate-x-1' aria-hidden='true'>→</span>
                </div>
                <h2 class='mt-8 text-2xl font-extrabold tracking-tight'>Explore stories</h2>
                <p class='mt-2 max-w-md leading-7 text-ink-600'>Search the community by claim stage, author, publication status, or activity.</p>
            </a>

            <a href='{{ route('profile.edit') }}' class='group surface-card p-7 transition hover:-translate-y-1 hover:border-brand-300 sm:p-8 md:col-span-2'>
                <div class='flex flex-wrap items-center justify-between gap-6'>
                    <div class='flex items-start gap-5'>
                        <span class='grid size-13 shrink-0 place-items-center rounded-2xl bg-brand-50 text-xl font-black text-brand-700' aria-hidden='true'>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span>
                        <div>
                            <h2 class='text-2xl font-extrabold tracking-tight'>Manage your profile</h2>
                            <p class='mt-2 leading-7 text-ink-600'>Update your name, email address, password, or account status.</p>
                        </div>
                    </div>
                    <span class='font-bold text-brand-700'>Account settings <span aria-hidden='true'>→</span></span>
                </div>
            </a>
        </div>

        <x-notice tone='info' class='mt-8'>
            Keep every contribution privacy-safe: remove claim numbers, contact details, payment data, medical information, and identifying documents.
        </x-notice>
    </section>
@endsection
