<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div class="text-2xl">
        <h1>{{ __('content.welcome')}}</h1>
    </div>

    @auth
    @php
        $user = Auth::user();  
        $currentTeam = $user->currentTeam;  
    @endphp
    @if(!$currentTeam || $user->can('update', $currentTeam))
    <div class="mt-6">
        {!! Str::markdown(__('content.welcome_text')) !!}
    </div>

    <div class="mt-6">
        <img src="{{ asset('dashboard.png') }}" />

        <h2>{{ __('content.onboarding_next_steps') }}</h2>
        <ol>
            <li>{{ __('content.onboarding_signup_to_witty') }} ✔️</li>  
            @if ($currentTeam)
            <li>{{ __('content.onboarding_create_team') }} ✔️</li>
            @else
            <li><a href="{{ route('teams.create') }}">{{ __('content.onboarding_create_team') }}</a></li>
            @endif 
            @if (!$currentTeam)
            <li>{{ __('content.onboarding_configure_organization_guidelines') }}</li>
            @elseif ($currentTeam && $currentTeam->organizationGuidelines)
            <li>{{ __('content.onboarding_configure_organization_guidelines') }} ✔️</li>
            @else
            <li><a href="{{ route('organization-guidelines', $currentTeam->id) }}">{{ __('content.onboarding_configure_organization_guidelines') }}</a></li>
            @endif 
            @if (!$currentTeam)
            <li>{{ __('content.onboarding_invite_users') }}</li>
            @elseif ($currentTeam && $currentTeam->total_user_licenses_count > 1)
            <li>{{ __('content.onboarding_invite_users') }} ✔️</li>
            @else
            <li><a href="{{ route('teams.show', $currentTeam->id) }}">{{ __('content.onboarding_invite_users') }}</a></li>
            @endif 
        </ol>
        @if (false)
        {{ __('content.onboarding_install_witty') }}
        @else
        <a href="https://www.witty.works/select-browser">{{ __('content.onboarding_install_witty') }}</a>
        @endif 
    </div>
    @endif

    @if($user->invitations->count())
    <div class="mt-6">
        {{ trans_choice('content.open_invitiations', $user->invitations->count()) }}
        @if($user->ownedTeams()->count())
        <div>
            {!! __('content.contact_support_to_delete_owned_teams') !!}
        </div>
        @else
        @if($user->allTeams()->count())
        <div>
            <strong>
                {{ __('content.accepting_invitation_will_result_in_leaving_your_current_team', ['team_name' => $user->allTeams()->first()->name]) }}
            </strong>
        </div>
        @endif
        <ul>
        @foreach($user->invitations as $invitation)
            <li>
                {{ $invitation->team->name }}
                <a href="{{ route('team-invitations.accept', ['invitation' => $invitation]) }}">
                    {{ __('content.accept_invitiation') }}
                </a>
                <a href="{{ route('team-invitations.reject', ['invitation' => $invitation]) }}">
                    {{ __('content.reject_invitiation') }}
                </a>
            </li>
        @endforeach
        </ul>
        @endif
    </div>
    @endif

    @endauth
</div>