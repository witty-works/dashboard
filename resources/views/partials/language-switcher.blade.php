@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale === LaravelLocalization::getCurrentLocale())
        <span class="footer-links margin-right" aria-current="true"><span lang="{{ $locale }}">{{ $supported_locale['native'] }}</span></span>
    @else
        <x-nav-link class="footer-links margin-right" href="{{ LaravelLocalization::getLocalizedURL($locale) }}"><span lang="{{ $locale }}">{{ $supported_locale['native'] }}</span></x-nav-link>
    @endif
@endforeach
