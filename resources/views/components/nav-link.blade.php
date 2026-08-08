@php
$classes = ($active ?? false) ? 'navigation-link navigation-link-active' : 'navigation-link';
@endphp

<a
    @if ($active ?? false) aria-current="page" @endif
    {{ $attributes->merge(['class' => $classes]) }}
    @if (!empty($target ?? '')) target="{{ $target }}" rel="noopener" @endif
>
    {{ $slot }}
</a>
