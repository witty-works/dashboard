<x-app-layout :pagetitle="__('errors.forbidden')">
    <div class="wittyworks-navigation-wrapper" role="navigation" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page lg:ml-20">
            <div class="pt-4 bg-gray-100">
                <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('errors.forbidden') }}
                    </h2>
                    <a href="/" class="m-5 btn btn-primary">{{ __('errors.back_button') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
