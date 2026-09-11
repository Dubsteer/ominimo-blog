@props([
    'autocomplete' => null,
    'bag' => 'default',
    'help' => null,
    'label',
    'name',
    'required' => false,
    'type' => 'text',
    'value' => null,
])

@php
    $errorBag = $errors->getBag($bag);
    $descriptionIds = collect([
        $help ? $name.'-help' : null,
        $errorBag->has($name) ? $name.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div>
    <label for='{{ $name }}' class='form-label'>
        {{ $label }}
        @unless ($required)
            <span class='font-medium text-ink-600'>(optional)</span>
        @endunless
    </label>

    <input
        id='{{ $name }}'
        name='{{ $name }}'
        type='{{ $type }}'
        @if ($type !== 'password') value='{{ old($name, $value) }}' @endif
        @if ($autocomplete) autocomplete='{{ $autocomplete }}' @endif
        @required($required)
        @if ($errorBag->has($name)) aria-invalid='true' @endif
        @if ($descriptionIds) aria-describedby='{{ $descriptionIds }}' @endif
        {{ $attributes->class(['form-control']) }}
    >

    @if ($help)
        <p id='{{ $name }}-help' class='form-help'>{{ $help }}</p>
    @endif

    @if ($errorBag->has($name))
        <p id='{{ $name }}-error' class='form-error'>{{ $errorBag->first($name) }}</p>
    @endif
</div>
