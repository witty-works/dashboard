<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.extension-check')
                @include('partials.invite-check')
                @include('partials.invitations')
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    @livewire('profile.update-profile-information-form')
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
