<x-jet-form-section submit="storeTermReplacement">
    <x-slot name="title">
        {{ __('guidelines.create_term_replacement') }}
    </x-slot>

    <x-slot name="description">
        @if($team->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($team) && !$team->subscribed())
           {!! __('guidelines.term_replacement_limit_reached', ['max_count' => $team->getTermReplacementsCount(), 'url' => route('teams.subscription')]) !!}
        @else
            {!! Str::markdown(__('guidelines.create_new_term_replacement_description')) !!}
        @endif
        </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label for="term" value="{!! __('guidelines.term_label') !!}" />

            <x-jet-input id="term_replacement_id"
                type="hidden" 
                wire:model.defer="term_replacement_id"
                autocomplete="term_replacement_id" />

            <x-jet-input id="term"
                type="textarea"
                class="mt-1 block w-full textarea-as-input"
                wire:model.defer="term"
                autocomplete="term"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')"
            />

            <x-jet-input-error for="term" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="replacement" value="{!! __('guidelines.replacement_label') !!}" />

            <x-jet-input id="replacement"
                type="textarea"
                class="mt-1 block w-full textarea-as-input"
                wire:model.defer="replacement"
                autocomplete="replacement"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')"
            />

            <x-jet-input-error for="replacement" class="mt-2" />
        </div>
{{--
        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />

            <x-select id="language_code"
                :options="\App\Models\TermReplacement::LANGUAGE_CODE"
                class="mt-1 block w-full"
                wire:model.defer="language_code"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')"
            />

                <x-jet-input-error for="language_code" class="mt-2" />
        </div>
--}}

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="explanation" value="{!! __('guidelines.explanation_label') !!}" />

            <x-jet-input id="explanation"
                type="textarea" 
                class="mt-1 block w-full"
                wire:model.defer="explanation"
                autocomplete="explanation"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')"
            />

            <x-jet-input-error for="explanation" class="mt-2" />
        </div>


        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="url" value="{!! __('guidelines.url_label') !!}" />

            <x-jet-input id="url"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="url"
                autocomplete="url"
            />

            <x-jet-input-error for="url" class="mt-2" />
        </div>


        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="emoji" value="{!! __('guidelines.emoji_label') !!}" />

            <x-jet-input id="emoji"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="emoji"
                autocomplete="emoji"
            />

            <x-jet-input-error for="emoji" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>