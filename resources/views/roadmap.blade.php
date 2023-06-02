<x-app-layout :pagetitle="__('content.roadmap')">
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20">
                token: {{ $token }}<br/>
                decoded: {!! print_r((array)\Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(config('productboard.private_key'), 'HS256')), true) !!}
                <iframe src="{{ $url }}?hide_logo=1&token={{ urlencode($token) }}" frameborder="0" class="h-screen w-full" title="{{ __('content.roadmap') }}"></iframe>
            </div>
        </div>
    </div>
</x-app-layout>
