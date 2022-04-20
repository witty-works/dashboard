<nav x-data="{ open: false }" class="navigation-wrapper">
    @php ($user = Auth::user())
    @php ($team = $user ? $user->currentTeam : null)
<!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="https://witty.works">
                        <x-jet-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                    @auth
                        <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                            {{ __('content.dashboard') }}
                        </x-jet-nav-link>

                        @if ($team)
                            <!-- Team Settings -->
                            @if($user->ownsTeam($team) && !$team->subscription())
                            <x-jet-nav-link href="{{ route('stripe.portal') }}">
                                {{ __('teams.subscribe') }}
                            </x-jet-nav-link>
                            @endif
                        @elseif(count($user->allTeams()) === 0)
                            @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                <x-jet-nav-link href="{{ route('teams.create') }}">
                                    {{ __('content.create_new_team') }}
                                </x-jet-nav-link>
                            @endcan
                        @endif
                    @else
                        <x-jet-nav-link href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                            {{ __('content.log_in') }} / {{ __('content.register') }}
                        </x-jet-nav-link>
                        <x-jet-nav-link href="https://www.witty.works/pricing">
                            {{ __('teams.pricing') }}
                        </x-jet-nav-link>
                    @endauth
            </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @include('partials/language-switcher')

                @auth
                <x-jet-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                            </button>
                        @else
                            <span class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                    {{ $user->name }}

                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <!-- Account Management -->
                        <x-jet-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('content.profile') }}
                        </x-jet-dropdown-link>

                        @lumki

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('content.api_tokens') }}
                            </x-jet-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100"></div>

                        @if ($team)
                        <!-- Team Management -->
                        <!-- Team Settings -->
                        <x-jet-dropdown-link href="{{ route('teams.show', $team->id) }}">
                            {{ __('content.team_settings') }}
                        </x-jet-dropdown-link>

                        @if($team->subscription())
                        <x-jet-dropdown-link href="{{ route('stripe.portal') }}">
                            {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                        </x-jet-dropdown-link>
                        @endif

                        <x-jet-dropdown-link href="{{ route('organization-guidelines', $team->id) }}">
                            {{ __('guidelines.organization_guidelines') }}
                        </x-jet-dropdown-link>

                        <x-jet-dropdown-link href="{{ route('term-replacement', $team->id) }}">
                            {{ __('guidelines.term_replacement_list') }}
                        </x-jet-dropdown-link>

                        <x-jet-dropdown-link href="{{ route('false-positive', $team->id) }}">
                            {{ __('guidelines.false_positive_list') }}
                        </x-jet-dropdown-link>
                        @endif

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-jet-dropdown-link href="{{ route('teams.create') }}">
                            {{ __('content.create_new_team') }}
                        </x-jet-dropdown-link>
                        @endcan

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <x-jet-dropdown-link href="{{ route('logout', ['provider' => 'azureadb2c']) }}">
                            {{ __('content.log_out') }}
                        </x-jet-dropdown-link>
                    </x-slot>
                </x-jet-dropdown>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-jet-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('content.dashboard') }}
            </x-jet-responsive-nav-link>
            @else
            <x-jet-responsive-nav-link href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                {{ __('content.log_in') }} / {{ __('content.register') }}
            </x-jet-responsive-nav-link>
            @endauth
        </div>

        @auth
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('content.profile') }}
                </x-jet-responsive-nav-link>

                @lumki

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-jet-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('content.api_tokens') }}
                    </x-jet-responsive-nav-link>
                @endif

                <!-- Team Management -->
                @if($team)
                <!-- Team Settings -->
                <x-jet-responsive-nav-link href="{{ route('teams.show', $team->id) }}" :active="request()->routeIs('teams.show')">
                    {{ __('content.team_settings') }}
                </x-jet-responsive-nav-link>

                @if($team->subscription())
                <x-jet-responsive-nav-link href="{{ route('stripe.portal') }}">
                    {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                </x-jet-responsive-nav-link>
                @endif

                <x-jet-responsive-nav-link href="{{ route('organization-guidelines', $team->id) }}">
                    {{ __('guidelines.organization_guidelines') }}
                </x-jet-responsive-nav-link>

                <x-jet-responsive-nav-link href="{{ route('term-replacement', $team->id) }}">
                    {{ __('guidelines.term_replacement_list') }}
                </x-jet-responsive-nav-link>

                <x-jet-responsive-nav-link href="{{ route('false-positive', $team->id) }}">
                    {{ __('guidelines.false_positive_list') }}
                </x-jet-responsive-nav-link>

                @endif

                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                    <x-jet-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                        {{ __('content.create_new_team') }}
                    </x-jet-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <x-jet-responsive-nav-link href="{{ route('logout', ['provider' => 'azureadb2c']) }}" @click.prevent="$root.submit();">
                    {{ __('content.log_out') }}
                </x-jet-responsive-nav-link>

                <div class="border-t border-gray-200"></div>

                <div class="block px-4 py-2 text-xs text-gray-400">
                    @include('partials/language-switcher')
                </div>
            </div>
        </div>
        @endauth
    </div>
</nav>
