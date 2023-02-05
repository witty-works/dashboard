<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners', ['hideInviteCheck' => true])
                <div class="ibarra-sub-title-h1 margin-top">
                    {{ __('content.show_team') }}
                </div>
                <div class="mt-5">
                @if (!Auth::user()->hasTeamPermission($team, 'update'))
                {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
                @endif

                @livewire('teams.team-member-manager-help-hero', ['team' => $team])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
