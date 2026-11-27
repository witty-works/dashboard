<x-app-layout :pagetitle="__('content.manage_members')">
    <x-slot name="navigation">
        @livewire('navigation-menu')
    </x-slot>

        <div class="wittyworks-page lg:ml-20">
            {{-- Here, not in the member manager, so that it comes before the
                 banners and whatever the manager shows. --}}
            <h1 class="ibarra-sub-title-h1 margin-top">{{ __('content.manage_members') }}</h1>

            @include('partials.banners', ['hideInviteCheck' => true])
            @if (!Auth::user()->hasTeamPermission($team, 'update'))
            <x-slot name="header">
                {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
            </x-slot>
            @endif

            @livewire('teams.team-member-manager-help-hero', ['team' => $team])

            @if (app(\App\Services\NlpApiKeys::class)->enabled() && Gate::check('updateTeamMember', $team))
                @livewire('teams.api-keys', ['team' => $team])
            @endif
        </div>
</x-app-layout>
