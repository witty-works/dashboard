<nav x-data="{ open: false }" class="navigation-wrapper">
<!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center h-16">
            <div class="flex">
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                    <x-jet-nav-link href="https://www.witty.works/terms">
                        {{ __('content.terms') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link href="https://www.witty.works/contact-sales">
                        {{ __('content.contact') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link href="https://www.witty.works/demo">
                        {{ __('content.book-demo') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link href="https://www.witty.works/privacy">
                        {{ __('content.privacy') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link href="https://www.witty.works/trust-and-security">
                        {{ __('content.trust-and-security') }}
                    </x-jet-nav-link>
                    <x-jet-nav-link href="https://www.witty.works/impressum">
                        {{ __('content.imprint') }}
                    </x-jet-nav-link>
                </div>
            </div>
        </div>
    </div>
</nav>
