<x-app-layout pagetitle="Subscription Management">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Subscription Management
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="py-10">
                    @livewire('subscription.form')
                </div>

                <div class="py-10">
                    @livewire('subscription.show')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
