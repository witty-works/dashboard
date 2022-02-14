@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale === LaravelLocalization::getCurrentLocale())
        <span class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 underline">{{ $supported_locale['native'] }}</span>
    @else
        <x-jet-nav-link href="{{ LaravelLocalization::getLocalizedURL($locale) }}"><span>{{ $supported_locale['native'] }}</span></x-jet-nav-link>
    @endif
@endforeach