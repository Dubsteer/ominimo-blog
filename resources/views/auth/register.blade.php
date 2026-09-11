@extends('layouts.app')

@section('title', 'Create account | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <div class='mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-soft lg:grid-cols-[0.85fr_1.15fr]'>
            <aside class='relative overflow-hidden bg-brand-950 p-8 text-white sm:p-10 lg:p-12' aria-labelledby='register-aside-heading'>
                <div class='pointer-events-none absolute -bottom-24 -right-24 size-64 rounded-full border-[3.5rem] border-brand-700/60' aria-hidden='true'></div>
                <p class='text-sm font-extrabold uppercase tracking-[0.13em] text-brand-200'>Join the discussion</p>
                <h2 id='register-aside-heading' class='mt-5 text-3xl font-extrabold leading-tight tracking-[-0.045em]'>Your experience can make the next step clearer.</h2>
                <ul class='mt-7 space-y-4 text-sm leading-6 text-brand-50'>
                    <li class='flex gap-3'><span class='grid size-6 shrink-0 place-items-center rounded-full bg-sun font-black text-ink-950' aria-hidden='true'>✓</span><span>Save a private draft before publishing.</span></li>
                    <li class='flex gap-3'><span class='grid size-6 shrink-0 place-items-center rounded-full bg-sun font-black text-ink-950' aria-hidden='true'>✓</span><span>Ask questions at any stage of the journey.</span></li>
                    <li class='flex gap-3'><span class='grid size-6 shrink-0 place-items-center rounded-full bg-sun font-black text-ink-950' aria-hidden='true'>✓</span><span>Keep identifying and sensitive details out.</span></li>
                </ul>
            </aside>

            <div class='p-7 sm:p-10 lg:p-12'>
                <p class='eyebrow'>Community account</p>
                <h1 class='mt-4 text-4xl font-extrabold tracking-[-0.05em] sm:text-5xl'>Create your account</h1>
                <p class='mt-4 max-w-lg leading-7 text-ink-600'>Use your account to publish stories and manage your contributions.</p>

                @if ($errors->any())
                    <x-notice tone='danger' class='mt-6' role='alert'>Some details need your attention. Review the fields below.</x-notice>
                @endif

                <form method='POST' action='{{ route('register') }}' class='mt-8 space-y-6'>
                    @csrf

                    <x-form-field name='name' label='Name' autocomplete='name' :required='true' autofocus />
                    <x-form-field name='email' label='Email address' type='email' autocomplete='email' :required='true' />
                    <x-form-field name='password' label='Password' type='password' autocomplete='new-password' :required='true' />
                    <x-form-field name='password_confirmation' label='Confirm password' type='password' autocomplete='new-password' :required='true' />

                    <x-button type='submit' size='lg' class='w-full'>Create account</x-button>
                </form>

                <p class='mt-7 text-center text-sm text-ink-600'>
                    Already registered?
                    <a href='{{ route('login') }}' class='font-bold text-brand-700 underline decoration-brand-300 decoration-2 underline-offset-4 hover:text-brand-900'>Log in</a>
                </p>
            </div>
        </div>
    </section>
@endsection
