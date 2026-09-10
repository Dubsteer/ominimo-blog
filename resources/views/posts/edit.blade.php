@extends('layouts.app')

@section('title', 'Edit post | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-5 py-16">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Post settings</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight">Edit your post</h1>
        <p class="mt-4 max-w-2xl text-slate-600">The public URL remains stable when you update the title.</p>

        <form method="POST" action="{{ route('posts.update', $post) }}" class="mt-10 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
            @include('posts._form', [
                'method' => 'PUT',
                'submitLabel' => 'Save changes',
            ])
        </form>
    </section>
@endsection
