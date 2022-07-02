<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('guidelines.language') }}
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
            <div class="w-full mb-14">
                <div class="py-6 guidelines-tagline">
                    <div class="text-body-color text-base leading-relaxed">
                        @if($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::CUSTOMIZE_WITTY)
                            @include('user/user-guidelines')
                        @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::TERM_REPLACEMENTS)
                            @include('user/term-replacement')
                        @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::FALSE_POSITIVES)
                            @include('user/false-positive')
                            @elseif($tab === \App\Http\Controllers\Livewire\UserGuidelinesController::DOMAINS)
                            @include('user/domain')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
