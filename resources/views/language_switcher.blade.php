@foreach($supported_locales as $locale => $supported_locale)
    @if($locale === $current_locale)
        <span class="ml-2 mr-2 text-gray-700">{{ $supported_locale['name'] }}</span>
    @else
        <a class="ml-1 underline ml-2 mr-2" href="/{{ $locale . substr(request()->path(), 2) }}">
            <span>{{ $supported_locale['name'] }}</span>
        </a>
    @endif
@endforeach
