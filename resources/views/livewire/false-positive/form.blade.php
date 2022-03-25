<x-jet-form-section submit="createFalsePositive">
    <x-slot name="title">
        {{ __('guidelines.create_false_positive') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.create_new_false_postive_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />

            <x-jet-input id="false_positive"
                type="text" 
                lass="mt-1 block w-full"
                wire:model.defer="false_positive"
                autocomplete="false_positive"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

                <x-jet-input-error for="false_positive" class="mt-2" />
        </div>
{{--
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />

            <x-select id="language_code"
                :options="\App\Models\FalsePositive::LANGUAGE_CODE"
                class="mt-1 block w-full"
                wire:model.defer="language_code"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

                <x-jet-input-error for="language_code" class="mt-2" />
        </div>
--}}
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('content.saved') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>