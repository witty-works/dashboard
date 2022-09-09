<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners')
            @if($team)
                @if($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::CUSTOMIZE_WITTY)
                    @include('teams/organization-guidelines')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::TERM_REPLACEMENTS)
                    @include('teams/term-replacement')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::FALSE_POSITIVES)
                    @include('teams/false-positive')
                @elseif($tab === \App\Http\Controllers\Livewire\OrganizationGuidelinesController::DOMAINS)
                        @include('teams/domain')
                @endif
            @else
            @include('partials.invitations')
            @endif
        </div>
    </div>
</x-app-layout>
