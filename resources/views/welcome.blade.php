@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    @php
        $postsIndexUrl = route('posts.index');
        $createPostUrl = route('posts.create');
        $registerUrl = route('register');
    @endphp
    <section class='page-shell page-section'>
        <div class='relative isolate overflow-hidden rounded-[2rem] bg-brand-600 px-6 py-12 text-white shadow-soft sm:px-10 sm:py-16 lg:grid lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:gap-14 lg:px-16 lg:py-20'>
            <div class='pointer-events-none absolute -right-24 -top-32 -z-10 size-96 rounded-full border-[5rem] border-white/10' aria-hidden='true'></div>
            <div>
                <p class='inline-flex items-center gap-2 text-sm font-extrabold uppercase tracking-[0.13em] text-brand-100'>
                    <span class='size-2 rounded-full bg-sun'></span>
                    Claims experience community
                </p>
                <h1 class='display-title mt-6'>A clearer way forward, shared.</h1>
                <p class='mt-7 max-w-2xl text-lg leading-8 text-brand-50 sm:text-xl'>
                    Ask questions and exchange practical, privacy-safe experiences about the insurance claim journey.
                </p>

                <div class='mt-9 flex flex-wrap gap-3'>
                    <x-button :href='$postsIndexUrl' size='lg' class='!bg-sun !text-ink-950 hover:!bg-white hover:!shadow-none'>
                        Explore stories <span aria-hidden='true'>→</span>
                    </x-button>
                    @auth
                        <x-button :href='$createPostUrl' size='lg' class='!border-white/35 !bg-white/10 !text-white hover:!bg-white/20' variant='secondary'>Share your experience</x-button>
                    @else
                        <x-button :href='$registerUrl' size='lg' class='!border-white/35 !bg-white/10 !text-white hover:!bg-white/20' variant='secondary'>Join the community</x-button>
                    @endauth
                </div>
            </div>

            <div class='relative mt-12 lg:mt-0' aria-label='A simple path from uncertainty to useful next steps'>
                <div class='rounded-[1.75rem] bg-white p-5 text-ink-950 shadow-2xl shadow-brand-950/20 sm:p-7'>
                    <div class='flex items-center justify-between gap-4 border-b border-brand-100 pb-5'>
                        <div>
                            <p class='text-sm font-bold text-ink-600'>Your claim journey</p>
                            <p class='mt-1 text-xl font-extrabold tracking-tight'>Find the next useful step</p>
                        </div>
                        <span class='grid size-12 shrink-0 place-items-center rounded-2xl bg-sun text-xl font-black' aria-hidden='true'>?</span>
                    </div>

                    <ol class='mt-2' role='list'>
                        <li class='grid grid-cols-[2.75rem_1fr] gap-4 py-4'>
                            <span class='grid size-11 place-items-center rounded-full bg-brand-600 font-extrabold text-white'>1</span>
                            <div><p class='font-extrabold'>Find your stage</p><p class='mt-1 text-sm leading-6 text-ink-600'>Browse experiences from people at the same point.</p></div>
                        </li>
                        <li class='grid grid-cols-[2.75rem_1fr] gap-4 border-y border-brand-100 py-4'>
                            <span class='grid size-11 place-items-center rounded-full bg-brand-100 font-extrabold text-brand-800'>2</span>
                            <div><p class='font-extrabold'>Compare perspectives</p><p class='mt-1 text-sm leading-6 text-ink-600'>Read questions, answers, and practical lessons.</p></div>
                        </li>
                        <li class='grid grid-cols-[2.75rem_1fr] gap-4 py-4'>
                            <span class='grid size-11 place-items-center rounded-full bg-brand-100 font-extrabold text-brand-800'>3</span>
                            <div><p class='font-extrabold'>Move with confidence</p><p class='mt-1 text-sm leading-6 text-ink-600'>Use the community’s knowledge to prepare your next move.</p></div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class='page-shell pb-20 sm:pb-28' aria-labelledby='community-values-heading'>
        <div class='grid gap-8 lg:grid-cols-[0.75fr_1.25fr] lg:items-start'>
            <div>
                <p class='eyebrow'>Built for useful conversations</p>
                <h2 id='community-values-heading' class='section-title mt-4'>Share the lesson, not the private details.</h2>
                <p class='mt-5 max-w-xl text-base leading-7 text-ink-600'>
                    This hub supports discussion only. It never asks for real claim references, policy numbers, financial data, or medical information.
                </p>
            </div>

            <div class='grid gap-4 sm:grid-cols-3'>
                <article class='surface-card p-6'>
                    <span class='grid size-11 place-items-center rounded-2xl bg-brand-50 text-xl font-black text-brand-700' aria-hidden='true'>01</span>
                    <h3 class='mt-5 text-lg font-extrabold'>Choose the stage</h3>
                    <p class='mt-2 text-sm leading-6 text-ink-600'>Keep every post connected to a clear part of the claim journey.</p>
                </article>
                <article class='surface-card p-6'>
                    <span class='grid size-11 place-items-center rounded-2xl bg-sun-light text-xl font-black text-ink-950' aria-hidden='true'>02</span>
                    <h3 class='mt-5 text-lg font-extrabold'>Remove identifiers</h3>
                    <p class='mt-2 text-sm leading-6 text-ink-600'>Leave out names, reference numbers, contact details, and documents.</p>
                </article>
                <article class='surface-card p-6'>
                    <span class='grid size-11 place-items-center rounded-2xl bg-brand-950 text-xl font-black text-white' aria-hidden='true'>03</span>
                    <h3 class='mt-5 text-lg font-extrabold'>Help someone else</h3>
                    <p class='mt-2 text-sm leading-6 text-ink-600'>Share what you learned and add constructive questions or comments.</p>
                </article>
            </div>
        </div>
    </section>
@endsection
