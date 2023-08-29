<nav x-data="{ open: false }" class="navigation-wrapper">
    <!-- navigation on large screens -->
    <div class="wittyworks-navigation-content-wrapper hidden lg:flex" aria-label="{{ __('content.desktop_navigation') }}">
        @include('partials.menu')
    </div>

    <!-- burger -->
    <div class="-mr-2 flex items-center">
        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md focus:outline-none transition" aria-label="{{ __('content.toggle_mobile_navigation') }}" aria-expanded="false" :aria-expanded="open.toString()">
            <svg class="h-6 w-6" stroke="black" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- navigation on small and medium screens -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden wittyworks-navigation-content-wrapper--burger" aria-label="{{ __('content.mobile_navigation') }}">
        @include('partials.menu')
    </div>
</nav>
