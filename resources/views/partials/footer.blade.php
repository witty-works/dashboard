@php
$resourceLinks = [
    __('content.trust-and-security') => 'https://www.witty.works/trust-and-security',
    __('content.terms') => 'https://www.witty.works/terms',
    __('content.privacy') => 'https://www.witty.works/privacy',
    __('content.imprint') => 'https://www.witty.works/imprint',
];

$contactLinks = [
    __('content.contact') => 'https://www.witty.works/contact-sales',
    __('content.book-demo') => 'https://www.witty.works/demo',
];
@endphp

<nav class="wittyworks-footer">
    <div class="wittyworks-footer-section">
        <div class="wittyworks-footer-section-title">{{ __('content.more_resouces') }}</div>
        @foreach($resourceLinks as $label => $url)
        <x-jet-nav-link class="wittyworks-footer-section-content" href="{{ $url }}" target="_blank" rel="noopener">
            {{ $label }}
        </x-jet-nav-link>
        @endforeach
    </div>
    <div class="wittyworks-footer-section">
        <div class="wittyworks-footer-section-title">{{ __('content.get_in_touch') }}</div>
        @foreach($contactLinks as $label => $url)
        <x-jet-nav-link class="wittyworks-footer-section-content" href="{{ $url }}" target="_blank" rel="noopener">
            {{ $label }}
        </x-jet-nav-link>
        @endforeach
    </div>
    <div class="wittyworks-footer-language-switcher">
        <img class="wittyworks-footer-language-switcher-icon" src="{{ url('svg/globe.svg') }}" alt="Select language" />
        @include('partials/language-switcher')
    </div>
</nav>
