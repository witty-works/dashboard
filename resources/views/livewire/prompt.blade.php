<x-form-section submit="sendPrompt">
    <x-slot name="title">
        {{ __('content.witty_prompt') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('content.prompt_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-input id="prompt"
                type="textarea"
                class="mt-1 block w-full"
                wire:model="prompt"
                autocomplete="off"
                aria-autocomplete="none" />

            <x-input-error wire:loading.remove for="prompt" class="mt-2" aria-describedby="prompt" />
        </div>

        @if($witty_response)
        <div class="align-middle items-center" wire:loading.remove>
            <h2>{{ __('content.witty_response') }}</h2>
            <div>
                {{ $witty_response }}
            </div>

            <h2>{{ __('content.edits') }}</h2>
            <div>
                {!! $diff !!}
            </div>
        </div>
        @endif
    </x-slot>

    <x-slot name="actions">
        <div class="flex flex-row align-middle items-center" role="toolbar" aria-label="Action buttons">
            <x-button>
                {{ __('content.submit') }}
            </x-button>

            <div wire:loading class="m-3"> 
                {{ __('content.send_prompt') }}
            </div>
        </div>
    </x-slot>
</x-form-section>