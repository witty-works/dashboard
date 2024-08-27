<x-app-layout :pagetitle="__('content.subscription')">
    <div class="wittyworks-navigation-wrapper" id="maincontent" role="navigation" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.banners')

                <h1 class="ibarra-sub-title-h1 margin-top">
                    {{ __('teams.plan_headline') }}
                </h1>

                <div>
                    @livewire('teams.plan-summary', ['team' => $team])
                </div>

                @if($team->subscribed() || $team->onGenericTrial())
                <div class="mt-10">
                    @livewire('teams.license-management', ['team' => $team])
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
