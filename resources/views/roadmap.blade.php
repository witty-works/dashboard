<x-app-layout :pagetitle="__('content.roadmap')">
    <nav class="wittyworks-navigation-wrapper" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </nav>
        <div class="wittyworks-page-wrapper" id="maincontent">
            <div class="wittyworks-page lg:ml-20">
                <iframe src="{{ $url }}?hide_logo=1&token={{ urlencode($token) }}" frameborder="0" class="h-screen w-full" title="{{ __('content.roadmap') }}"></iframe>
            </div>
        </div>
    </div>
</x-app-layout>
