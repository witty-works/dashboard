@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale === LaravelLocalization::getCurrentLocale())
        <x-jet-nav-link class="navigation-link--language" :active="request()->segment(1) === $locale"><span>{{ $supported_locale['native'] }}</span></x-jet-nav-link>
    @else
        <x-jet-nav-link class="navigation-link--language" :active="request()->is('/en')" href="{{ LaravelLocalization::getLocalizedURL($locale) }}"><span>{{ $supported_locale['native'] }}</span></x-jet-nav-link>
    @endif
@endforeach