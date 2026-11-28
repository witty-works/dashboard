@php
// The deployment's own legal documents, where it provides them; its imprint
// is part of its privacy policy.
$legal = \App\Support\LegalDocument::provided();
$resourceLinks = array_filter([
    __('content.terms') => $legal['terms_of_service'] ? route('terms.show') : null,
    __('content.privacy') => $legal['privacy_policy'] ? route('policy.show') : null,
]);

@endphp

<footer class="wittyworks-footer light-grey-background" aria-label="{{ __('content.footer_aria_label') }}">

    @if ($resourceLinks)
    <section class="wittyworks-footer-section" aria-labelledby="more-resources-title">
        <h2 id="more-resources-title" class="lato-small-paragraph-title-h4 margin-bottom">{{ __('content.more_resouces') }}</h2>
        <nav aria-labelledby="more-resources-title">
            @foreach($resourceLinks as $label => $url)
            <x-nav-link class="footer-links" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ __('content.opens_in_new_tab', ['label' => $label]) }}">
                {{ $label }}
            </x-nav-link>
            @endforeach
        </nav>
    </section>
    @endif


    <nav class="wittyworks-footer-language-switcher" aria-label="{{ __('content.language_switcher_aria_label') }}">
        @include('partials/language-switcher')
    </nav>
</footer>
