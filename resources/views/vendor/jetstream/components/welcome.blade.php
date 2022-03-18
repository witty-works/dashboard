<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div class="text-2xl">
        <h1>{{ __('content.welcome')}}</h1>
    </div>

    <div class="mt-6">
        {!! Str::markdown(__('content.welcome_text')) !!}
    </div>

    @auth
    @php
        $user = Auth::user();  
        $currentTeam = $user->currentTeam;  
    @endphp
    @if($user->invitations->count())
        {{ trans_choice('content.open_invitiations', $user->invitations->count()) }}
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
        <ul>
    @elseif(!$currentTeam || $user->can('update', $currentTeam))
    <div class="mt-6">
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
            @elseif ($currentTeam && $currentTeam->totalUserCount() > 1)
            <li>{{ __('content.onboarding_invite_users') }} ✔️</li>
            @else
            <li><a href="{{ route('teams.show', $currentTeam->id) }}">{{ __('content.onboarding_invite_users') }}</a></li>
            @endif 
        </ol>

        @if (false)
        {{ __('content.onboarding_install_witty') }} ✔️</li>
        @else
        <a href="https://www.witty.works/select-browser">{{ __('content.onboarding_install_witty') }}</a>
        @endif 
    </div>
    @endif
    @endauth
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
    <div class="p-6">
        <div class="flex items-center">
            <img src="{{ asset('svg/witty-icon-color-line-inverted.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_1_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_1_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/039-computation-analysis.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_2_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_2_text')) !!}

                @guest
                {!! __('content.login_cta', ['url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login'])]) !!}
                @endguest
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200">
        <div class="flex items-center">
            <img src="{{ asset('svg/015-group-of-people.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_3_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_3_text')) !!}
            </div>
        </div>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-l">
        <div class="flex items-center">
            <img src="{{ asset('svg/077-business-value.svg') }}" width="30" />
            <div class="ml-4 text-lg leading-7 font-semibold">
                {{ __('content.section_4_title') }}
            </div>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm">
                {!! Str::markdown(__('content.section_4_text')) !!}
            </div>
        </div>
    </div>
</div>
