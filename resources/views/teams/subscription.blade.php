<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.banners')

                <div class="ibarra-sub-title-h1 margin-top">
                    {{ __('teams.plan_headline') }}
                </div>   
                @livewire('teams.plan-summary', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
