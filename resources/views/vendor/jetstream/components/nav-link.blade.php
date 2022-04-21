@props(['active'])

@php
$classes = ($active ?? false) ? 'navigation-link-active' : 'navigation-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
