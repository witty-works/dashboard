@php
$resourceLinks = [
    __('content.trust-and-security') => 'https://www.witty.works/trust-and-security',
    __('content.terms') => 'https://www.witty.works/terms',
    __('content.privacy') => 'https://www.witty.works/privacy',
    __('content.imprint') => 'https://www.witty.works/imprint',
];

$contactLinks = [
    __('content.contact') => 'https://www.witty.works/contact',
    __('content.book-demo') => 'https://www.witty.works'.(app()->getLocale() === 'en' ? '' : ('/'.app()->getLocale())).'/demo',
    __('content.help') => 'https://www.witty.works/en/help/wittys-help-center',
];
@endphp

<footer class="wittyworks-footer light-grey-background" aria-label="{{ __('content.footer_aria_label') }}">

    <section class="wittyworks-footer-section" aria-labelledby="more-resources-title">
        <h4 id="more-resources-title" class="lato-small-paragraph-title-h4 margin-bottom">{{ __('content.more_resouces') }}</h4>
        <nav>
            @foreach($resourceLinks as $label => $url)
            <x-nav-link class="footer-links" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">
                {{ $label }}
            </x-nav-link>
            @endforeach
        </nav>
    </section>

    <section class="wittyworks-footer-section" aria-labelledby="get-in-touch-title">
        <h4 id="get-in-touch-title" class="lato-small-paragraph-title-h4 margin-bottom">{{ __('content.get_in_touch') }}</h4>
        <nav>
            @foreach($contactLinks as $label => $url)
            <x-nav-link class="footer-links" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">
                {{ $label }}
            </x-nav-link>
            @endforeach
        </nav>
    </section>

    <div class="wittyworks-footer-language-switcher" aria-label="{{ __('content.language_switcher_aria_label') }}">
        @include('partials/language-switcher')
    </div>
</footer>
