@foreach(LaravelLocalization::getSupportedLocales() as $locale => $supported_locale)
    @if($locale === LaravelLocalization::getCurrentLocale())
        <span class="ml-2 mr-2 text-gray-700">{{ $supported_locale['native'] }}</span>
    @else
        <a class="ml-1 underline ml-2 mr-2" href="{{ LaravelLocalization::getLocalizedURL($locale) }}">
            <span>{{ $supported_locale['native'] }}</span>
        </a>
    @endif
@endforeach