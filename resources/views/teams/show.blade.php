<x-app-layout :pagetitle="__('content.manage_members')">
    <div class="wittyworks-navigation-wrapper" id="maincontent" role="navigation" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners', ['hideInviteCheck' => true])
            @if (!Auth::user()->hasTeamPermission($team, 'update'))
            <x-slot name="header">
                {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
            </x-slot>
            @endif

            @livewire('teams.team-member-manager-help-hero', ['team' => $team])
        </div>
    </div>
</x-app-layout>
