@props(['value'])

<label {{ $attributes->merge(['class' => 'lato-paragraph-text-p']) }}>
    {!! $value ?? $slot !!}
</label>
