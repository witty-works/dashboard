<x-app-layout :pagetitle="__('content.privacy_policy')">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">{{ __('content.privacy_policy') }}</h1>
        <div class="bg-white p-6 rounded shadow prose max-w-none">
            @if ($content !== null)
                {!! $content !!}
            @else
                <p>{{ __('content.privacy_policy_not_published') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
