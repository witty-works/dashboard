<x-app-layout pagetitle="Subscription Management">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Subscription Management
        </h2>
    </x-slot>

    <div class="py-10">
        @livewire('subscription.form')
    </div>

    <div class="py-10">
        @livewire('subscription.show')
    </div>
</x-app-layout>
