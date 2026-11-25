@php
    $nativeNames = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français'];
    // The current page in each other language. A page the localizer cannot
    // translate, such as the 404 page of the fallback route, has no other
    // language to switch to, and the switcher is left out.
    $switchUrls = [];
    try {
        foreach (config('localizer.supported_locales') as $locale) {
            if ($locale !== app()->getLocale()) {
                $switchUrls[$locale] = Route::localizedSwitcherUrl($locale);
            }
        }
    } catch (\LogicException $exception) {
        $switchUrls = null;
    }
@endphp
@if ($switchUrls !== null)
    @foreach (config('localizer.supported_locales') as $locale)
        @if ($locale === app()->getLocale())
            <span class="footer-links margin-right" aria-current="true"><span lang="{{ $locale }}">{{ $nativeNames[$locale] ?? $locale }}</span></span>
        @else
            <x-nav-link class="footer-links margin-right" href="{{ $switchUrls[$locale] }}"><span lang="{{ $locale }}">{{ $nativeNames[$locale] ?? $locale }}</span></x-nav-link>
        @endif
    @endforeach
@endif
