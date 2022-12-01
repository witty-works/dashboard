<div class="flex flex-row align-middle items-center">
    <x-jet-button>
        {{ __('content.save') }}
    </x-jet-button>

    <a wire:click="cancel()" class="button secondary-button-red ">
        {{ __('content.cancel') }}
    </a>

    <x-jet-action-message class="m-3" on="saved">
        {{ __('content.saved') }}
    </x-jet-action-message>
</div>