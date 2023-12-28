@php
$classes = ($active ?? false) ? 'navigation-link navigation-link-active' : 'navigation-link';
@endphp

<nav>
    <a 
        role="link"
        aria-current="{{ $active ?? false ? 'page' : 'false' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>
</nav>
