<?php 
    $tabs = [      
        'customize-witty',
        'term-replacements',
        'ignore-words',
        'domains',
    ];
?>

<script type="text/javascript">
    window.onload = function() {
        const personalAccount = document.getElementById('personal_account');
        const personalAccountContent = document.getElementById('personal_account_content');
        const teamAccount = document.getElementById('team_account');
        const teamAccountContent = document.getElementById('team_account_content');

        personalAccount.addEventListener('click', function() {
            personalAccountContent.style.display = 'block';
            teamAccountContent.style.display = 'none';
        });

        teamAccount.addEventListener('click', function() {
            personalAccountContent.style.display = 'none';
            teamAccountContent.style.display = 'block';
        });
    }
</script>

<nav x-data="{ open: false }" class="navigation-wrapper">
    @php ($user = Auth::user())
    @php ($team = $user ? $user->currentTeam : null)
    <div>
        <div>
            <div class="wittyworks-navigation-content-wrapper">

                <a href="https://www.witty.works/">
                    <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
                </a>

                @auth
                <!-- LOGGED IN -->
                <div class="wittyworks-navigation-top-half">
                    <div class="wittyworks-navigation-account-toggl-wrapper">
                        <div id="personal_account" class="wittyworks-navigation-account-toggl">{{ __('guidelines.personal_account') }}</div>
                        <div class="wittyworks-navigation-account-divider">|</div>
                        <div id="team_account" class="wittyworks-navigation-account-toggl">{{ __('guidelines.team_account') }}</div>
                    </div>

                    <div id="personal_account_content" style="display:block">
                        <div class="wittyworks-navigation-link-wrapper">
                            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                            {{ __('guidelines.language') }}
                        </div>

                        <div class="wittyworks-navigation-sub-wrapper">
                            @foreach($tabs as $key => $route)
                            <a class="wittyworks-navigation-sub-link" href="{{ route('user.' . $route) }}">{{ __("guidelines.{$route}_label") }}</a>
                            <br>
                            @endforeach
                        </div>
                    </div>

                    @if ($team && Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
                    <div id='team_account_content' style="display:none">
                        <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="Team Settings" />
                            {{ __('content.manage_members') }}
                        </x-jet-nav-link>

                        <div class="wittyworks-navigation-link-wrapper">
                            <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                            {{ __('teams.language') }}            
                        </div>

                        <div class="wittyworks-navigation-sub-wrapper">
                            @foreach($tabs as $key => $route)
                                <a class="wittyworks-navigation-sub-link" href="{{ route('teams.' . $route) }}">{{ __("guidelines.{$route}_label") }}</a>
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
                
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/en/categories" target="_blank" rel="noopener">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="Academy" />
                        {{ __('content.academy') }}
                    </x-jet-nav-link>

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('profile.show') }}">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/dollar.svg') }}" alt="Account" />
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
        </div>
    </div>



         
</nav>
