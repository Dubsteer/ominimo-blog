@extends('layouts.app')

@section('title', 'Log in | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-lg px-5 py-16">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Welcome back</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight">Log in to your account</h1>
            <p class="mt-3 text-slate-600">Continue sharing useful, privacy-safe claim experiences with the community.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                @csrf

                <x-form-field name="email" label="Email address" type="email" autocomplete="email" :required="true" autofocus />
                <x-form-field name="password" label="Password" type="password" autocomplete="current-password" :required="true" />

                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <input name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                    Remember me
                </label>

                <button type="submit" class="w-full rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
                    Log in
                </button>
            </form>

            <p class="mt-7 text-center text-sm text-slate-600">
                New here?
                <a href="{{ route('register') }}" class="font-semibold text-slate-950 underline decoration-amber-400 decoration-2 underline-offset-4">Create an account</a>
            </p>
        </div>
    </section>
@endsection
