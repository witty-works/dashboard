@php
    // During a Livewire update the "current URL" is the /livewire endpoint, so
    // getLocalizedURL() would emit broken /{locale}/livewire/... links if this
    // partial is ever rendered inside a Livewire component
    // (mcamara/laravel-localization#773). Build from the page URL instead.
    $localizableUrl = request()->hasHeader('X-Livewire')
        ? (request()->header('Referer') ?: url('/'))
        : null;
@endphp
@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale === LaravelLocalization::getCurrentLocale())
        <span class="footer-links margin-right" aria-current="true"><span lang="{{ $locale }}">{{ $supported_locale['native'] }}</span></span>
    @else
        <x-nav-link class="footer-links margin-right" href="{{ LaravelLocalization::getLocalizedURL($locale, $localizableUrl) }}"><span lang="{{ $locale }}">{{ $supported_locale['native'] }}</span></x-nav-link>
    @endif
@endforeach
