@php
$links = [
    __('content.contact') => 'https://www.witty.works/contact-sales',
    __('content.book-demo') => 'https://www.witty.works/demo',
    'Roadmap' => 'https://roadmap.witty.works',
    __('content.trust-and-security') => 'https://www.witty.works/trust-and-security',
    __('content.terms') => 'https://www.witty.works/termss',
    __('content.privacy') => 'https://www.witty.works/privacy',
    __('content.imprint') => 'https://www.witty.works/imprint',
];
@endphp
@if(isset($type) && $type === 'hamburger')
@foreach($links as $label => $url)
<x-jet-responsive-nav-link href="{{ $url }}">
    {{ $label }}
</x-jet-responsive-nav-link>
@endforeach
@else
<nav x-data="{ open: false }" class="navigation-wrapper">
<!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center h-16">
            <div class="flex">
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                    @foreach($links as $label => $url)
                    <x-jet-nav-link href="{{ $url }}">
                        {{ $label }}
                    </x-jet-nav-link>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</nav>
@endif