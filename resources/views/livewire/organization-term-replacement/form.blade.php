<x-jet-form-section submit="storeTermReplacement">
    <x-slot name="title">
        <h2 id="organization_term_replacements">{{ __('guidelines.create_term_replacement') }}</h2>
    </x-slot>

    <x-slot name="description">
        @if($model->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($model) && !$model->subscribed())
            {!! __('guidelines.term_replacement_limit_reached', ['max_count' => $model->getTermReplacementsCount(), 'url' => route('teams.subscription')]) !!}
        @else
            {!! Str::markdown(__('guidelines.create_new_term_replacement_description')) !!}
        @endif
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label id="term-label" for="term" value="{{ __('guidelines.term_label') }}" />
            <x-jet-input id="term" type="textarea" class="mt-1 block w-full" wire:model.defer="term" aria-describedby="term-label" />
            <x-jet-input-error for="term" id="termError" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="replacement_label" for="replacement" value="{{ __('guidelines.replacement_label') }}" />
            <x-jet-input id="replacement" type="textarea" class="mt-1 block w-full textarea-as-input" wire:model.defer="replacement" aria-describedby="replacement_label" />
            <x-jet-input-error for="replacement" id="replacementError" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="language_code_label" for="language_code" value="{{ __('guidelines.language_code_label') }}" />
            <x-select id="language_code" :options="$language_codes" class="mt-1 block w-full" wire:model.defer="language_code" aria-describedby="language_code_label" />
            <x-jet-input-error for="language_code" id="languageCodeError" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="matching_type_label" for="matching_type" value="{{ __('guidelines.matching_type_label') }}" />
            <x-select id="matching_type" :options="\App\Models\TermReplacement::MATCHING_TYPES" class="mt-1 block w-full" wire:model.defer="matching_type" aria-describedby="matching_type_label" />
            <x-jet-input-error for="matching_type" id="matchingTypeError" />
        </div>

        @if($show_word_type)
        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="word_type_label" for="word_type" value="{{ __('guidelines.word_type_label') }}" />
            <x-select id="word_type" :options="\App\Models\TermReplacement::WORD_TYPES" class="mt-1 block w-full" wire:model.defer="word_type" />
        </div>
        @endif

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="explanation_label" for="explanation" value="{{ __('guidelines.explanation_label') }}" />
            <x-jet-input id="explanation" type="textarea" class="mt-1 block w-full" wire:model.defer="explanation" aria-describedby="explanation_label" />
            <x-jet-input-error for="explanation" id="explanationError" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="url_label" for="url" value="{{ __('guidelines.url_label') }}" />
            <x-jet-input id="url" type="text" class="mt-1 block w-full" wire:model.defer="url" aria-describedby="url_label" />
            <x-jet-input-error for="url" id="urlError" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label id="emoji_label" for="emoji" value="{!! __('guidelines.emoji_label') !!}" />
            <x-jet-input id="emoji" type="text" class="mt-1 block w-full" wire:model.defer="emoji" aria-describedby="emoji_label" />
            <x-jet-input-error for="emoji" id="emojiError" />
        </div>
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
</x-jet-form-section>