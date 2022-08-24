<?php 
    $tabs = [      
        'customize-witty',
        'term-replacements',
        'ignore-words',
        'domains',
    ];
?>
<nav x-data="{ open: false }" class="navigation-wrapper">
    @php ($user = Auth::user())
    @php ($team = $user ? $user->currentTeam : null)
    <div>
        <div>
            <div class="wittyworks-navigation-content-wrapper">

                <a href="{{ route('dashboard') }} ">
                    <img class="wittyworks-logo" src="{{ url('svg/witty-logo-white.svg') }}" alt="Witty Works" />
                </a>

                @auth
                <!-- LOGGED IN -->
                <div class="wittyworks-navigation-top-half">
                    <div class="wittyworks-navigation-account-toggl-wrapper">
                        <div>{{ __('guidelines.personal_account') }}</div>
                        <div class="wittyworks-navigation-account-divider">|</div>
                        <div>{{ __('guidelines.team_account') }}</div>
                    </div>

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('user.language-guidelines') }}">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                        {{ __('guidelines.language') }}
                    </x-jet-nav-link>

                    <div class="wittyworks-navigation-sub-wrapper">
                        @foreach($tabs as $key => $route)
                        <a class="wittyworks-navigation-sub-link" href="{{ route('user.' . $route) }}">{{ __("guidelines.{$route}_label") }}</a>
                        <br>
                        @endforeach
                    </div>

                    @if ($team && Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.show') }}" :active="request()->routeIs('teams.show')">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/team.svg') }}" alt="Team Settings" />
                        {{ __('content.team_settings') }}
                    </x-jet-nav-link>

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('teams.language-guidelines') }}" :active="request()->routeIs('teams.language-guidelines')">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/language.svg') }}" alt="Language" />
                        {{ __('teams.language') }}            
                    </x-jet-nav-link>

                    <div class="wittyworks-navigation-sub-wrapper">
                        @foreach($tabs as $key => $route)
                            <a class="wittyworks-navigation-sub-link" href="{{ route('teams.' . $route) }}">{{ __("guidelines.{$route}_label") }}</a>
                            <br>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="wittyworks-navigation-bottom-half">
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/editor" target="_blank" rel="noopener">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/editor.svg') }}" alt="Editor" />
                        {{ __('content.witty_editor') }}
                    </x-jet-nav-link>
                
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/en/categories">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="Academy" />
                        {{ __('content.academy') }}
                    </x-jet-nav-link>

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('profile.show') }}">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/user.svg') }}" alt="Account" />
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

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/en/categories" target="_blank" rel="noopener">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/bulb.svg') }}" alt="Academy" />
                        {{ __('content.academy') }}
                    </x-jet-nav-link>
                </div>
                <div class="wittyworks-navigation-bottom-half">
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="https://www.witty.works/pricing" target="_blank" rel="noopener">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/star.svg') }}" alt="Witty Works" />
                        {{ __('teams.pricing') }}
                    </x-jet-nav-link>

                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register']) }}">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/register.svg') }}" alt="Witty Works" />
                        {{ __('content.register') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                        <img class="wittyworks-navigation-icon" src="{{ url('svg/navigationIcons/login.svg') }}" alt="Witty Works" />
                        {{ __('content.log_in') }}
                    </x-jet-nav-link>
                </div>
                @endauth
            </div>



            <!-- STILL WIP BELOW THIS LINE -->
             <!-- TODO -->
                        <!-- @if($user->ownsTeam($team) && !$team->subscribed())
                            <x-jet-nav-link class="wittyworks-navigation-link-wrapper" href="{{ route('stripe.portal') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold tracking-widest hover:bg-red-600 active:bg-red-600 focus:outline-none focus:border-transparent disabled:opacity-25 transition">
                                {{ __('teams.subscribe') }}
                            </x-jet-nav-link>
                        @endif -->
                @auth
                <x-jet-dropdown>
                    <x-slot name="trigger">
                    </x-slot>
                    <x-slot name="content">
                        @lumki

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('content.api_tokens') }}
                            </x-jet-dropdown-link>
                        @endif

                        @if ($team && Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
                        <x-jet-dropdown-link href="{{ route('teams.subscription') }}">
                            {{ __('teams.subscription') }}
                        </x-jet-dropdown-link>

                        @endif

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-jet-dropdown-link href="{{ route('teams.create') }}">
                            {{ __('content.create_new_team') }}
                        </x-jet-dropdown-link>
                        @endcan
                       
                    </x-slot>
                </x-jet-dropdown>
                @endauth
            </div>

            <!-- Hamburger -->
            <!-- <div class="-mr-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="white" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div> -->
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <!-- <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-jet-responsive-nav-link href="https://www.witty.works/editor" target="_blank" rel="noopener">
                {{ __('content.witty_editor') }}
            </x-jet-responsive-nav-link>
            @else
            <x-jet-responsive-nav-link href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                {{ __('content.log_in') }} / {{ __('content.register') }}
            </x-jet-responsive-nav-link>
            @endauth
        </div>


    </div> -->
</nav>
