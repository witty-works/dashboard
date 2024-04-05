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
    <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
</a>

@auth
<nav aria-label="{{ __('content.main_navigation_aria_label') }}">
    <div class="wittyworks-navigation-container">
        @if ($team_edit)
            <div class="wittyworks-navigation-account-toggle-wrapper">
                <x-nav-link id="personal_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route($personalRoute) }}" :active="$open_tab === 'user'">
                    {{ __('guidelines.personal_account') }}
                </x-nav-link>
                <div class="wittyworks-navigation-account-divider">|</div>
                 <x-nav-link id="team_account" class="wittyworks-navigation-item lato-small-paragraph-title-h4" href="{{ route($teamRoute) }}" :active="$open_tab === 'team'">
                    {{ __('guidelines.team_account') }}
                </x-nav-link>
            </div>
        @endif 

        @if (count($user->allSwitchableTeams()) > 1)
        <div class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" style="cursor: default">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="" />
            {{ __('content.choose_team') }}
        </div>
        <div class="wittyworks-navigation-sub-wrapper">
            @foreach ($user->allSwitchableTeams() as $team)
                <x-switchable-team :team="$team" />
            @endforeach
        </div>
        @endif

        @if ($open_tab === 'user')
        <div id="personal_account_content" aria-label="{{ __('content.personal_account_content') }}">
                <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('profile.show') }}" :active="request()->routeIs('user.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/user.svg') }}" alt="" />
                    {{ __('content.manage_account') }}
                </x-nav-link>

                @if(!$team_edit && $team && $team->user_access_to_team_analytics)
                    <div class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" style="cursor: default">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="" />
                        {{ __('content.analytics') }}
                    </div>

                    <div class="wittyworks-navigation-sub-wrapper">
                        <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.analytics') }}" :active="request()->routeIs('user.analytics')">{{ __('guidelines.personal_account') }}</x-nav-link>
                        <br />
                        <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.analytics') }}" :active="request()->routeIs('teams.analytics')">{{ __('guidelines.team_account') }}</x-nav-link>
                    </div>
                @else
                    <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('user.analytics') }}" :active="request()->routeIs('user.analytics')">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="" />
                        {{ __('content.analytics') }}
                    </x-nav-link>
                @endif

                @if($user->isUserLicensedToTeam($team))
                @foreach($links as $route => $label)
                @if($loop->first)
                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{!! $label !!}</x-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                @else
                    <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('user.' . $route) }}" :active="request()->routeIs('user.' . $route)">{!! $label !!}</x-nav-link>
                    <br />
                @endif
                @endforeach
                </div>
                @endif
            </div>
            @endif

        @if ($open_tab === 'team')
            <div id='team_account_content' aria-label="{{ __('content.team_account_content_aria_label') }}">
                <x-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="" />
                    {{ __('content.manage_members') }}
                </x-nav-link>

                <x-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.subscription') }}" :active="request()->routeIs('teams.subscription')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/dollar.svg') }}" alt="" />
                    {{ __('content.subscription') }}
                </x-nav-link>

                <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('teams.analytics') }}" :active="request()->routeIs('teams.analytics')">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/analytics.svg') }}" alt="" />
                    {{ __('content.analytics') }}
                </x-nav-link>

                @foreach($links as $route => $label)
                @if($loop->first)
                <div class="wittyworks-navigation-label-wrapper lato-paragraph-text-p">
                    <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="" />
                    <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{!! $label !!}</x-nav-link>
                </div>

                <div class="wittyworks-navigation-sub-wrapper">
                @else
                    <x-nav-link class="wittyworks-navigation-sub-link lato-small-text-p" href="{{ route('teams.' . $route) }}" :active="request()->routeIs('teams.' . $route)">{!! $label !!}</x-nav-link>
                    <br />
                @endif
                @endforeach
                </div>
            </div>
        @endif
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('editor') }}">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="" />
            {{ __('content.witty_editor') }}
        </x-nav-link>
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('academy') }}" :active="request()->routeIs('academy')">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="" />
            {{ __('content.academy') }}
        </x-nav-link>

        @if (Session::get('login_source') !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/logout.svg') }}" alt="" />
            {{ __('content.log_out') }}
        </x-nav-link>
        @endif
        <div class="wittyworks-navigation-username lato-small-text-p">
            {{ $user->name }}
        </div>
    </div>
</nav>

    @if(config('lumki.show_lumki') || app('impersonate')->isImpersonating())
        @lumki

        @can(config('lumki.lumkiPermission'))
        <a href="{{ route('subscriptions') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
            Subscriptions
        </a>
        @endcan
    @endif

@else
    <!-- LOGGED OUT -->
    <div class="wittyworks-navigation-bottom-half">
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="https://www.witty.works/pricing" target="_blank" rel="noopener">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/star.svg') }}" alt="" />
            {{ __('teams.pricing') }}
        </x-nav-link>
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/login.svg') }}" alt="" />
            {{ __('content.log_in') }}
        </x-nav-link>
        <x-nav-link class="wittyworks-navigation-link-wrapper lato-paragraph-text-p" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register']) }}">
            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/register.svg') }}" alt="" />
            {{ __('content.register') }}
        </x-nav-link>
    </div>
@endauth
