<x-jet-form-section submit="storeTermReplacement">
    <x-slot name="title">
        {{ __('guidelines.create_term_replacement') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.create_new_term_replacement_description')) !!}

        @if($team->term_replacements_limit_reached)
        {!! __('guidelines.term_replacement_limit_reached', ['max_count' => $team->term_replacements_count, 'url' => route('stripe.portal')]) !!}
        @endif
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="term" value="{!! __('guidelines.term_label') !!}" />

            <x-jet-input id="term_replacement_id"
                type="hidden" 
                wire:model.defer="term_replacement_id"
                autocomplete="term_replacement_id" />

            <x-jet-input id="term"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="term"
                autocomplete="term"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="term" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="replacement" value="{!! __('guidelines.replacement_label') !!}" />

            <x-jet-input id="replacement"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="replacement"
                autocomplete="replacement"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="replacement" class="mt-2" />
        </div>
{{--
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />

            <x-select id="language_code"
                :options="\App\Models\TermReplacement::LANGUAGE_CODE"
                class="mt-1 block w-full"
                wire:model.defer="language_code"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

                <x-jet-input-error for="language_code" class="mt-2" />
        </div>
--}}

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="explanation" value="{!! __('guidelines.explanation_label') !!}" />

            <x-jet-input id="explanation"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="explanation"
                autocomplete="explanation"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="explanation" class="mt-2" />
        </div>


        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="url" value="{!! __('guidelines.url_label') !!}" />

            <x-jet-input id="url"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="url"
                autocomplete="url"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="url" class="mt-2" />
        </div>


        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="emoji" value="{!! __('guidelines.emoji_label') !!}" />

            <x-jet-input id="emoji"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="emoji"
                autocomplete="emoji"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="emoji" class="mt-2" />
        </div>

    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>