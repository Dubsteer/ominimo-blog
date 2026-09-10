@extends('layouts.app')

@section('title', 'Create account | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-lg px-5 py-16">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Join the discussion</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight">Create your account</h1>
            <p class="mt-3 text-slate-600">Do not include claim numbers, payment details, or sensitive personal information in public content.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6">
                @csrf

                <x-form-field name="name" label="Name" autocomplete="name" :required="true" autofocus />
                <x-form-field name="email" label="Email address" type="email" autocomplete="email" :required="true" />
                <x-form-field name="password" label="Password" type="password" autocomplete="new-password" :required="true" />
                <x-form-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" :required="true" />

                <button type="submit" class="w-full rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
                    Create account
                </button>
            </form>

            <p class="mt-7 text-center text-sm text-slate-600">
                Already registered?
                <a href="{{ route('login') }}" class="font-semibold text-slate-950 underline decoration-amber-400 decoration-2 underline-offset-4">Log in</a>
            </p>
        </div>
    </section>
@endsection
