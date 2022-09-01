<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page">
                @include('partials.extension-check')
                @include('partials.invite-check')
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
</x-app-layout>
