<?php
    use Illuminate\Support\Facades\Auth;

    $links = [
        'category-settings' => __('guidelines.language'),
        'language-settings' => __('guidelines.language_settings_label'),
        'privacy-settings' => __('guidelines.privacy_settings_label'),
        'dictionary' => __('guidelines.dictionary_label'),
        'ignored-words' => __('guidelines.ignore_words_label'),
    ];
    
    $user = Auth::user();
    $team = $user ? $user->currentTeam : null;
    $team_edit = $team && Auth::user()->hasTeamPermission($team, 'edit_guidelines');
    $open_tab = $team_edit && (strpos(request()->path(), 'team') || strpos(request()->path(), 'livewire') !== false)
        ? 'team' : 'user';

    $routeName = Route::currentRouteName();
    $route = explode('.', $routeName);
    $mapableRoutes = [
        'analytics',
        'category-settings',
        'language-settings',
        'dictionary',
        'ignored-words',
        'privacy-settings',
    ];

    $personalRoute = 'user.language-guidelines';
    $teamRoute = 'teams.language-guidelines';

    if ($route[0] === 'user') {
        $personalRoute = $routeName;
        if (isset($route[1]) && in_array($route[1], $mapableRoutes)) {
            $teamRoute = 'teams.'.$route[1];
        }
    } elseif ($route[0] === 'teams') {
        $teamRoute = $routeName;
        if (isset($route[1]) && in_array($route[1], $mapableRoutes)) {
            $personalRoute = 'user.'.$route[1];
        }
    }
?>
<a class="skip-link" href="#maincontent">{{ __('content.skip_to_main_content') }}</a>


<a href="https://www.witty.works/">
    <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="{{ __('content.witty_logo_alt') }}" />
</a>

@auth
<nav aria-label="{{ __('content.main_navigation_aria_label') }}">
    <div class="wittyworks-navigation-top-half">
        @if ($team_edit)
            <div class="wittyworks-navigation-account-toggle-wrapper">
                <x-jet-nav-link id="personal_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route($personalRoute) }}" :active="$open_tab === 'user'">
                    {{ __('guidelines.personal_account') }}
                </x-jet-nav-link>
                <div class="wittyworks-navigation-account-divider">|</div>
                 <x-jet-nav-link id="team_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route($teamRoute) }}" :active="$open_tab === 'team'">
                    {{ __('guidelines.team_account') }}
                </x-jet-nav-link>
            </div>
        @endif 

        @if ($open_tab === 'user')
        <div id="personal_account_content" aria-label="{{ __('content.personal_account_content') }}">
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('profile.show') }}" :active="request()->routeIs('user.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/user.svg') }}" alt="{{ __('content.manage_account_alt') }}" />
                    {{ __('content.manage_account') }}
                </x-jet-nav-link>
    
                @if(!$team_edit && $team && $team->user_access_to_team_analytics)
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('user.analytics') }}">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="{{ __('content.analytics_alt') }}" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>

                <div class="wittyworks-navigation-sub-wrapper">
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.analytics') }}" :active="request()->routeIs('user.analytics')">{{ __('guidelines.personal_account') }}</x-jet-nav-link>
                    <br />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.analytics') }}" :active="request()->routeIs('teams.analytics')">{{ __('guidelines.team_account') }}</x-jet-nav-link>
                </div>
                @else
                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('user.analytics') }}" :active="request()->routeIs('user.analytics')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="{{ __('content.analytics_alt') }}" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>
                @endif

                @foreach($links as $route => $label)
                @if($loop->first)
                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="{{ __('content.language_settings_alt') }}" />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{{ $label }}</x-jet-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                @else
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br />
                @endif
                @endforeach
                </div>
            </div>
            @endif        

        @if ($open_tab === 'team')
        <div id='team_account_content' aria-label="{{ __('content.team_account_content_aria_label') }}">
        <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="{{ __('content.manage_team_alt') }}" />
                    {{ __('content.manage_members') }}
                </x-jet-nav-link>

                <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.subscription') }}" :active="request()->routeIs('teams.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/dollar.svg') }}" alt="{{ __('content.subscription_alt') }}" />
                    {{ __('content.subscription') }}
                </x-jet-nav-link>

                <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('teams.analytics') }}" :active="request()->routeIs('teams.analytics')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="{{ __('content.analytics_alt') }}" />
                    {{ __('content.analytics') }}
                </x-jet-nav-link>

                @foreach($links as $route => $label)
                @if($loop->first)
                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="{{ __('content.language_settings_alt') }}" />
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{{ $label }}</x-jet-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                @else
                    <x-jet-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{{ $label }}</x-jet-nav-link>
                    <br />
                @endif
                @endforeach
                </div>
            </div>
        @endif
    </div>
</nav>

    @if(config('lumki.show_lumki') || app('impersonate')->isImpersonating())
    @lumki
    @endif

    <div class="wittyworks-navigation-bottom-half">
        <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('editor') }}">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="{{ __('content.witty_editor_alt') }}" />
            {{ __('content.witty_editor') }}
        </x-jet-nav-link>
        <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('academy') }}" :active="request()->routeIs('academy')">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="{{ __('content.academy_alt') }}" />
                {{ __('content.academy') }}
            </x-jet-nav-link>
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/logout.svg') }}" alt="{{ __('content.log_out_alt') }}" />
                {{ __('content.log_out') }}
            </x-jet-nav-link>
            <div class="wittyworks-navigation-username lato-small-text-p">
                {{ $user->name }}
            </div>
        </div>
        @else
        <!-- LOGGED OUT -->
        <div class="wittyworks-navigation-top-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('editor') }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="{{ __('content.witty_editor_alt') }}" />
                {{ __('content.witty_editor') }}
            </x-jet-nav-link>
        </div>
        <div class="wittyworks-navigation-bottom-half">
            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="https://www.witty.works/pricing" target="_blank" rel="noopener">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/star.svg') }}" alt="{{ __('content.pricing_alt') }}" />
                {{ __('teams.pricing') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/login.svg') }}" alt="{{ __('content.log_in_alt') }}" />
                    {{ __('content.log_in') }}
            </x-jet-nav-link>

            <x-jet-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register']) }}">
                <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/register.svg') }}" alt="{{ __('content.register_alt') }}" />
                    {{ __('content.register') }}
            </x-jet-nav-link>
        </div>
        @endauth
