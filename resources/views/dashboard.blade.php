@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-16">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Your dashboard</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight">Hello, {{ auth()->user()->name }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-slate-600">
            Manage your profile and contribute privacy-safe experiences to the community.
        </p>

        <div class="mt-10 grid gap-5 md:grid-cols-2">
            <a href="{{ route('profile.edit') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-400">
                <h2 class="text-xl font-bold">Manage your profile</h2>
                <p class="mt-2 text-slate-600">Update your account details, password, or account status.</p>
            </a>

            <a href="{{ route('posts.create') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-400">
                <h2 class="text-xl font-bold">Create a post</h2>
                <p class="mt-2 text-slate-600">Share a question or experience as a draft or published discussion.</p>
            </a>
        </div>
    </section>
@endsection
