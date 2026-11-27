<x-app-layout :pagetitle="__('content.terms_of_service')">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">{{ __('content.terms_of_service') }}</h1>
        <div class="bg-white p-6 rounded shadow prose max-w-none">
            @if ($content !== null)
                {!! $content !!}
            @else
                <p>{{ __('content.terms_of_service_not_published') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
