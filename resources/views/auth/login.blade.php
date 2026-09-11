@extends('layouts.app')

@section('title', 'Log in | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <div class='mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-soft lg:grid-cols-[0.85fr_1.15fr]'>
            <aside class='relative overflow-hidden bg-brand-600 p-8 text-white sm:p-10 lg:p-12' aria-labelledby='login-aside-heading'>
                <div class='pointer-events-none absolute -bottom-24 -right-24 size-64 rounded-full border-[3.5rem] border-white/10' aria-hidden='true'></div>
                <p class='text-sm font-extrabold uppercase tracking-[0.13em] text-brand-100'>Welcome back</p>
                <h2 id='login-aside-heading' class='mt-5 text-3xl font-extrabold leading-tight tracking-[-0.045em]'>Good conversations move claim journeys forward.</h2>
                <p class='mt-5 max-w-sm leading-7 text-brand-50'>Return to your drafts, share an experience, or join a discussion with the community.</p>
                <a href='{{ route('posts.index') }}' class='mt-8 inline-flex items-center gap-2 font-bold text-white underline decoration-brand-300 decoration-2 underline-offset-4'>
                    Browse public stories <span aria-hidden='true'>→</span>
                </a>
            </aside>

            <div class='p-7 sm:p-10 lg:p-12'>
                <p class='eyebrow'>Member access</p>
                <h1 class='mt-4 text-4xl font-extrabold tracking-[-0.05em] sm:text-5xl'>Log in to your account</h1>
                <p class='mt-4 max-w-lg leading-7 text-ink-600'>Continue sharing useful, privacy-safe claim experiences.</p>

                @if ($errors->any())
                    <x-notice tone='danger' class='mt-6' role='alert'>We could not log you in. Check the details below and try again.</x-notice>
                @endif

                <form method='POST' action='{{ route('login') }}' class='mt-8 space-y-6'>
                    @csrf

                    <x-form-field name='email' label='Email address' type='email' autocomplete='email' :required='true' autofocus />
                    <x-form-field name='password' label='Password' type='password' autocomplete='current-password' :required='true' />

                    <label class='flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold text-ink-600'>
                        <input name='remember' type='checkbox' value='1' class='size-5 rounded-md border-brand-300 text-brand-600 focus:ring-brand-300'>
                        Remember me on this device
                    </label>

                    <x-button type='submit' size='lg' class='w-full'>Log in</x-button>
                </form>

                <p class='mt-7 text-center text-sm text-ink-600'>
                    New here?
                    <a href='{{ route('register') }}' class='font-bold text-brand-700 underline decoration-brand-300 decoration-2 underline-offset-4 hover:text-brand-900'>Create an account</a>
                </p>
            </div>
        </div>
    </section>
@endsection
