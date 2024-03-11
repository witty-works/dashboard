<x-app-layout :pagetitle="__('content.subscription')">
    <div class="wittyworks-navigation-wrapper" id="maincontent">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.banners')

                <div class="ibarra-sub-title-h1 margin-top">
                    {{ __('teams.plan_headline') }}
                </div>

                <div>
                    @livewire('teams.plan-summary', ['team' => $team])
                </div>

                <div class="mt-10">
                    @livewire('teams.license-management', ['team' => $team])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
