<x-app-layout pagetitle="Subscriptions">
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div>
            <div class="py-10">
                @livewire('subscription.form')
            </div>

            <div class="py-10">
                @livewire('subscription.show')
            </div>
        </div>
    </div>
</x-app-layout>