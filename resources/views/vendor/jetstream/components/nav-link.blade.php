@php
$classes = ($active ?? false) ? 'navigation-link navigation-link-active' : 'navigation-link';
@endphp

<nav aria-label="Primary Navigation">
    <a 
        role="link"
        aria-current="{{ $active ?? false ? 'page' : 'false' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>
</nav>
