@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <section class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-20 lg:grid-cols-[1.1fr_0.9fr] lg:py-28">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Claims experience community</p>
            <h1 class="mt-5 text-5xl font-bold tracking-tight text-slate-950 sm:text-6xl">Share what you learned. Help someone move forward.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                Exchange practical, privacy-safe experiences about the insurance claim journey. This community is for discussion only and does not process claims or payments.
            </p>

            <div class="mt-9 flex flex-wrap gap-4">
                <a href="{{ route('posts.index') }}" class="rounded-full border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-950 hover:border-amber-400">Browse posts</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Open dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Create an account</a>
                @endauth
            </div>
        </div>

        <aside class="rounded-3xl bg-amber-300 p-8 shadow-sm sm:p-10" aria-labelledby="privacy-heading">
            <h2 id="privacy-heading" class="text-2xl font-bold">Keep public posts privacy-safe</h2>
            <ul class="mt-6 space-y-4 text-slate-800">
                <li class="flex gap-3"><span aria-hidden="true">&#10003;</span><span>Describe the claim stage and what helped.</span></li>
                <li class="flex gap-3"><span aria-hidden="true">&#10003;</span><span>Remove names, claim numbers, and contact details.</span></li>
                <li class="flex gap-3"><span aria-hidden="true">&#10003;</span><span>Never publish payment or medical information.</span></li>
            </ul>
        </aside>
    </section>
@endsection
