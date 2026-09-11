@extends('layouts.app')

@section('title', 'Create post | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-5 py-16">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">New discussion</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight">Share a claim experience</h1>
        <p class="mt-4 max-w-2xl text-slate-600">Focus on the process and lessons learned. Save a draft if you still need to remove identifying details.</p>

        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="mt-10 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
            @include('posts._form', [
                'method' => 'POST',
                'post' => null,
                'submitLabel' => 'Create post',
            ])
        </form>
    </section>
@endsection
