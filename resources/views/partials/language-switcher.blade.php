@php
    $nativeNames = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français'];
@endphp
@foreach (config('localizer.supported_locales') as $locale)
    @if ($locale === app()->getLocale())
        <span class="footer-links margin-right" aria-current="true"><span lang="{{ $locale }}">{{ $nativeNames[$locale] ?? $locale }}</span></span>
    @else
        <x-nav-link class="footer-links margin-right" href="{{ Route::localizedSwitcherUrl($locale) }}"><span lang="{{ $locale }}">{{ $nativeNames[$locale] ?? $locale }}</span></x-nav-link>
    @endif
@endforeach
