<x-jet-form-section submit="storeFalsePositive">
    <x-slot name="title">
        {{ __('guidelines.create_false_positive') }}
    </x-slot>

    <x-slot name="description">
        @if($team->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($team) && !$team->subscribed())
            {!! __('guidelines.false_positive_limit_reached', ['max_count' => $team->getFalsePositivesCount(), 'url' => route('stripe.portal')]) !!}
        @else
            {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}
        @endif
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />

            <x-jet-input id="term_replacement_id"
                type="hidden" 
                wire:model.defer="term_replacement_id"
                autocomplete="term_replacement_id" />

            <x-jet-input id="false_positive"
                type="text" 
                class="mt-1 block w-full"
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
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>