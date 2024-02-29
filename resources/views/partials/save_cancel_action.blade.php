<div class="flex flex-row align-middle items-center" role="toolbar" aria-label="Action buttons">

    <x-button>
        {{ __('content.save') }}
    </x-button>

    <button wire:click="cancel()" class="button secondary-button-red">
        {{ __('content.cancel') }}
    </button>

    <x-action-message class="m-3" on="saved" role="status" aria-live="polite">
        {{ __('content.saved') }}
    </x-action-message>
</div>
