<?php
    $tabs = [      
        __('guidelines.language_settings_label') => 'language-settings',
        __('guidelines.dictionary_label') => 'dictionary',
        __('guidelines.ignore_words_label') => 'ignored-words',
        __('guidelines.privacy_settings_label') => 'privacy-settings',
    ];
?>

<nav x-data="{ open: false }" class="navigation-wrapper">
    @php ($user = Auth::user())
    @php ($team = $user ? $user->currentTeam : null)
    <div class="wittyworks-navigation-content-wrapper">
        <a href="https://www.witty.works/">
            <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
        </a>

        @auth
        <!-- LOGGED IN -->
        <div class="wittyworks-navigation-top-half">
            <div class="wittyworks-navigation-account-toggl-wrapper">
                <x-jet-nav-link id="personal_account" class="wittyworks-navigation-account-toggl" href="{{ route('user.language-settings') }}" :active="strpos(request()->path(), 'user') !== false">
                    {{ __('guidelines.personal_account') }}
                </x-jet-nav-link>
                <div class="wittyworks-navigation-account-divider">|</div>
                 <x-jet-nav-link id="team_account" class="wittyworks-navigation-account-toggl"  href="{{ route('teams.language-settings') }}" :active="strpos(request()->path(), 'team') !== false">
                    {{ __('guidelines.team_account') }}
                </x-jet-nav-link>
            </div>

            <div id="personal_account_content" style="display: <?=strpos(request()->path(), 'user') !== false ? 'block' : 'none' ?>">
                <div class="wittyworks-navigation-link-wrapper">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                    {{ __('guidelines.language') }}
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($tabs as $label => $route)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br>
                    @endforeach
                </div>
            </div>

            @if ($team && Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
            <div id='team_account_content' style="display: <?=strpos(request()->path(), 'team') !== false ? 'block' : 'none' ?>">
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="Team Settings" />
                    {{ __('content.manage_members') }}
                </x-jet-nav-link>
                <div class="wittyworks-navigation-link-wrapper">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                    {{ __('teams.language') }}            
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                    @foreach($tabs as $label => $route)
                    <x-jet-nav-link class="wittyworks-navigation-sub-link" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="wittyworks-navigation-bottom-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/editor" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="Editor" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
                
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('academy') }}" alt="Academy" :active="request()->routeIs('academy')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="Academy"/>
                {{ __('content.academy') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route(strpos(request()->path(), 'team') !== false ? 'teams.subscription' : 'user.subscription') }}" :active="request()->routeIs('user.subscription')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/dollar.svg') }}" alt="Subscription" />
                {{ __('content.manage_account') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/logout.svg') }}" alt="Witty Works" />
                {{ __('content.log_out') }}
            </x-jet-nav-link>
            <div class="wittyworks-navigation-username">{{ $user->name }}</div>
        </div>
                
        @else
        <!-- LOGGED OUT -->
        <div class="wittyworks-navigation-top-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/editor" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="Editor" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
        </div>
        <div class="wittyworks-navigation-bottom-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/pricing" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/star.svg') }}" alt="Witty Works" />
                {{ __('teams.pricing') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/login.svg') }}" alt="Witty Works" />
                    {{ __('content.log_in_register') }}
            </x-jet-nav-link>
        </div>
        @endauth
    </div>
</nav>
