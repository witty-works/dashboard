@props(['submit'])

<div>
    <x-jet-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-jet-section-title>

    <div class="guidelines-wrapper">
        <form wire:submit.prevent="{{ $submit }}">
            <div class="px-4 py-5 bg-white sm:p-6 shadow {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                {{ $form }}
            </div>

            @if (isset($actions))
                <div class="guidelines-enable-for-all">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
