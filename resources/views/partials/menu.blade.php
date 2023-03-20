<?php
    use Illuminate\Support\Facades\Auth;

    $links = [      
        'language-settings' => __('guidelines.language_settings_label'),
        'dictionary' => __('guidelines.dictionary_label'),
        'ignored-words' => __('guidelines.ignore_words_label'),
        'privacy-settings' => __('guidelines.privacy_settings_label'),
    ];
    

    $user = Auth::user();
    $team = $user ? $user->currentTeam : null;
    $team_edit = $team && Auth::user()->hasTeamPermission($team, 'edit_guidelines');
    $open_tab = $team_edit && (strpos(request()->path(), 'team') || strpos(request()->path(), 'livewire') !== false)
        ? 'team' : 'user';
?>

        <a href="https://www.witty.works/">
            <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
        </a>

        @auth
        <!-- LOGGED IN -->
        @if($user->hasCompletedOnboarding())
        <div class="wittyworks-navigation-top-half">
            <x-jet-nav-link class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route('profile.show') }}">
                {{ $user->name}}
            </x-jet-nav-link>
            @if ($team_edit)
            <br />
            <x-jet-nav-link class="wittyworks-team-name lato-paragraph-text-p " href="{{ route('teams.show') }}">
                {{ $team->name }}
            </x-jet-nav-link>
            @elseif ($team)
            <br />
            <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                {{ $team->name }}
            </div>
            @endif

            @if ($team_edit)
            <div class="wittyworks-navigation-account-toggle-wrapper">
                <x-jet-nav-link id="personal_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route('user.language-settings') }}" :active="$open_tab === 'user'">
                    {{ __('guidelines.personal_account') }}
                </x-jet-nav-link>
                <div class="wittyworks-navigation-account-divider">|</div>
                 <x-jet-nav-link id="team_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route('teams.language-settings') }}" :active="$open_tab === 'team'">
                    {{ __('guidelines.team_account') }}
                </x-jet-nav-link>
            </div>
            @endif

            @if ($open_tab === 'user')
            <div id="personal_account_content">
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('profile.show') }}" :active="request()->routeIs('user.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/user.svg') }}" alt="" />
                    {{ __('content.manage_account') }}
                </x-jet-nav-link>
    
                @if(!$team_edit && $team && $team->user_access_to_team_analytics)
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('user_analytics') }}">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="analytics" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>

                <div class="wittyworks-navigation-sub-wrapper">
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user_analytics') }}" :active="request()->routeIs('user_analytics')">{{ __('guidelines.personal_account') }}</x-jet-nav-link>
                    <br />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('team_analytics') }}" :active="request()->routeIs('team_analytics')">{{ __('guidelines.team_account') }}</x-jet-nav-link>
                </div>
                @else
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('user_analytics') }}" :active="request()->routeIs('user_analytics')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="analytics" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>
                @endif

                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . key($links)) }}">{{ __('guidelines.language') }}</x-jet-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($links as $route => $label)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br />
                    @endforeach
                </div>
            </div>
            @endif

            @if ($open_tab === 'team')
            <div id='team_account_content'>
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="" />
                    {{ __('content.manage_members') }}
                </x-jet-nav-link>

                <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.subscription') }}" :active="request()->routeIs('teams.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/dollar.svg') }}" alt="" />
                    {{ __('content.subscription') }}
                </x-jet-nav-link>

                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('team_analytics') }}" :active="request()->routeIs('team_analytics')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="analytics" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>

                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . key($links)) }}">{{ __('teams.language') }}</x-jet-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($links as $route => $label)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br />
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(config('lumki.show_lumki'))
        @lumki
        @endif

        <div class="wittyworks-navigation-bottom-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('editor') }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
            @if($user->hasCompletedOnboarding())
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('academy') }}" alt="Academy" :active="request()->routeIs('academy')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt=""/>
                {{ __('content.academy') }}
            </x-jet-nav-link>
            @endif
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/logout.svg') }}" alt="" />
                {{ __('content.log_out') }}
            </x-jet-nav-link>
            <div class="wittyworks-navigation-username lato-small-text-p">{{ $user->name }}</div>
        </div>
        @else
        <!-- LOGGED OUT -->
        <div class="wittyworks-navigation-top-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('editor') }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
        </div>
        <div class="wittyworks-navigation-bottom-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/pricing" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/star.svg') }}" alt="" />
                {{ __('teams.pricing') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/login.svg') }}" alt="" />
                    {{ __('content.log_in_register') }}
            </x-jet-nav-link>
        </div>
        @endauth