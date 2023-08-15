<div class="flex flex-row align-middle items-center" role="toolbar" aria-label="Action buttons">

    <x-jet-button aria-label="{{ __('content.save_aria_label') }}">
        {{ __('content.save') }}
    </x-jet-button>

    <button wire:click="cancel()" class="button secondary-button-red" aria-label="{{ __('content.cancel_aria_label') }}">
        {{ __('content.cancel') }}
    </button>

    <x-jet-action-message class="m-3" on="saved" role="status" aria-live="polite">
        {{ __('content.saved') }}
    </x-jet-action-message>
</div>
