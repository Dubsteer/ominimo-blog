@props([
    'autocomplete' => null,
    'bag' => 'default',
    'label',
    'name',
    'required' => false,
    'type' => 'text',
    'value' => null,
])

@php($errorBag = $errors->getBag($bag))

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-800">
        {{ $label }}
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @required($required)
        @if ($errorBag->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class([
            'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition',
            'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errorBag->has($name),
            'border-slate-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-100' => ! $errorBag->has($name),
        ]) }}
    >

    @if ($errorBag->has($name))
        <p id="{{ $name }}-error" class="mt-2 text-sm font-medium text-red-700">{{ $errorBag->first($name) }}</p>
    @endif
</div>
