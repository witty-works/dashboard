<?php
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
<nav x-data="{ open: false }" class="navigation-wrapper">
    <div class="wittyworks-navigation-content-wrapper">
        <a href="https://www.witty.works/">
            <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
        </a>

        @auth
        <!-- LOGGED IN -->
        <div class="wittyworks-navigation-top-half">
            @if ($team_edit)
            <x-jet-nav-link class="wittyworks-team-name" href="{{ route('teams.subscription') }}#updateTeamName">
                    {{ $team->name }}
                </x-jet-nav-link>
            <div class="wittyworks-navigation-account-toggle-wrapper">
                <x-jet-nav-link id="personal_account" class="wittyworks-navigation-account-toggle" href="{{ route('user.language-settings') }}" :active="$open_tab === 'user'">
                    {{ __('guidelines.personal_account') }}
                </x-jet-nav-link>
                <div class="wittyworks-navigation-account-divider">|</div>
                 <x-jet-nav-link id="team_account" class="wittyworks-navigation-account-toggle" href="{{ route('teams.language-settings') }}" :active="$open_tab === 'team'">
                    {{ __('guidelines.team_account') }}
                </x-jet-nav-link>
            </div>
            @elseif ($team)
            <div class="wittyworks-navigation-label-wrapper">
                {{ $team->name }}
            </div>
            @endif

            @if ($open_tab === 'user')
            <div id="personal_account_content">
                <div class="wittyworks-navigation-label-wrapper">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    {{ __('guidelines.language') }}
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($links as $route => $label)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br>
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
    
                <div class="wittyworks-navigation-label-wrapper">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    {{ __('teams.language') }}            
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($links as $route => $label)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @lumki

        <div class="wittyworks-navigation-bottom-half">
            @if($user->ownsTeam($team) && !$team->subscribed())
            <x-jet-nav-link href="{{ route('stripe.portal') }}" class="wittyworks-navigation-link-wrapper inline-flex items-center px-4 py-2 mb-100 bg-red-500 border border-transparent rounded-md font-semibold tracking-widest hover:bg-red-600 active:bg-red-600 focus:outline-none focus:border-transparent disabled:opacity-25 transition">
                {{ __('teams.subscribe') }}
            </x-jet-nav-link>
            @endif
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/editor" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
                
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('academy') }}" alt="Academy" :active="request()->routeIs('academy')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt=""/>
                {{ __('content.academy') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('profile.show') }}" :active="request()->routeIs('user.subscription')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/user.svg') }}" alt="" />
                {{ __('content.manage_account') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/logout.svg') }}" alt="" />
                {{ __('content.log_out') }}
            </x-jet-nav-link>
            <div class="wittyworks-navigation-username">{{ $user->name }}</div>
        </div>
                
        @else
        <!-- LOGGED OUT -->
        <div class="wittyworks-navigation-top-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/editor" target="_blank" rel="noopener">
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
    </div>
</nav>
