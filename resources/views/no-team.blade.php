<x-app-layout :pagetitle="__('content.no_team_title')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('content.no_team_title') }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
        @if (Auth::user()->invitations->count())
            @include('partials.invitations', ['user' => Auth::user()])
        @else
            <p>{{ __('content.no_team_body') }}</p>
        @endif
    </div>
</x-app-layout>
