<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('teams.language') }}
        </h2>

        <div class="mt-10">
        @foreach($tabs as $key => $route)
        <a href="{{ route($route) }}" class="rounded-t-md border py-6 px-4 lg:px-6 tab-navigation-link{{ $key === $tab ? ' tab-navigation-link-active' : ''}} color:black">
            {{ __("guidelines.{$key}_label") }}
        </a>
        @endforeach
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($team)
            <div class="w-full mb-14">
                <div class="py-6 guidelines-tagline">
                    <div class="text-body-color text-base leading-relaxed">
                        @if($tab === \App\Http\Controllers\Livewire\GuidelinesController::CUSTOMIZE_WITTY)
                            @include('organization-guidelines')
                        @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::TERM_REPLACEMENTS)
                            @include('term-replacement')
                        @elseif($tab === \App\Http\Controllers\Livewire\GuidelinesController::FALSE_POSITIVES)
                            @include('false-positive')
                        @endif
                    </div>
                </div>
            </div>
            @else
            @include('partials.onboarding')
            @endif
        </div>
    </div>
</x-app-layout>
