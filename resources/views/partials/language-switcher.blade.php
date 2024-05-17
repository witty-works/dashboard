@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale !== 'fr' || config('app.french_support'))
    @if($locale === LaravelLocalization::getCurrentLocale())
        <x-nav-link class="footer-links margin-right" :active="request()->segment(1) === $locale"><span>{{ $supported_locale['native'] }}</span></x-nav-link>
    @else
        <x-nav-link class="footer-links margin-right" :active="request()->is('/en')" href="{{ LaravelLocalization::getLocalizedURL($locale) }}"><span>{{ $supported_locale['native'] }}</span></x-nav-link>
    @endif
    @endif
@endforeach