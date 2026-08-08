<x-form-section submit="storeTermReplacement">

    <x-slot name="title">
        <h2 id="term_replacements">{{ __('guidelines.create_term_replacement') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p>
                {!! Str::markdown(__('guidelines.create_new_term_replacement_description')) !!}
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-label for="term" value="{!! __('guidelines.term_label') !!}" />
            <x-input id="term_replacement_id"
                         type="hidden"
                         wire:model="term_replacement_id"
                         autocomplete="off"
                         aria-autocomplete="none" />
            <x-input id="term"
                         type="textarea"
                         class="mt-1 block w-full textarea-as-input"
                         wire:model="term"
                         autocomplete="off"
                         aria-autocomplete="none"
                         @error('term') aria-invalid="true" aria-describedby="term-error" @enderror />
            <x-input-error for="term" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="replacement" value="{!! __('guidelines.replacement_label') !!}" />
            <x-input id="replacement"
                         type="textarea"
                         class="mt-1 block w-full textarea-as-input"
                         wire:model="replacement"
                         autocomplete="off"
                         aria-autocomplete="none"
                         @error('replacement') aria-invalid="true" aria-describedby="replacement-error" @enderror />
            <x-input-error for="replacement" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />
            <x-select id="language_code"
                      :options="$language_codes"
                      class="mt-1 block w-full"
                      wire:model="language_code"
                      @error('language_code') aria-invalid="true" aria-describedby="language_code-error" @enderror />
            <x-input-error for="language_code" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="matching_type">{!! __('guidelines.matching_type_label') !!}</x-label>
            <x-select id="matching_type"
                      :options="\App\Models\TermReplacement::MATCHING_TYPES"
                      class="mt-1 block w-full"
                      wire:model="matching_type"
                      wire:change="showHideWordType"
                      @error('matching_type') aria-invalid="true" aria-describedby="matching_type-error" @enderror />
            <x-input-error for="matching_type" class="mt-2" />
            <x-input-error for="word_type" class="mt-2" />
        </div>

        <!-- Word Type Section -->
        @if($show_word_type)
            <div class="w-full col-span-6 sm:col-span-4 mt-5">
                <x-label for="word_type" value="{!! __('guidelines.word_type_label') !!}" />
                <x-select id="word_type"
                          :options="\App\Models\TermReplacement::WORD_TYPES"
                          class="mt-1 block w-full"
                          wire:model="word_type"
                          @error('word_type') aria-invalid="true" aria-describedby="word_type-error" @enderror />
            </div>
        @endif

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="explanation" value="{!! __('guidelines.explanation_label') !!}" />
            <x-input id="explanation"
                         type="textarea"
                         class="mt-1 block w-full"
                         wire:model="explanation"
                         autocomplete="off"
                         aria-autocomplete="none"
                         @error('explanation') aria-invalid="true" aria-describedby="explanation-error" @enderror />
            <x-input-error for="explanation" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="url" value="{!! __('guidelines.url_label') !!}" />
            <x-input id="url"
                         type="text"
                         class="mt-1 block w-full"
                         wire:model="url"
                         autocomplete="off"
                         aria-autocomplete="none"
                         @error('url') aria-invalid="true" aria-describedby="url-error" @enderror />
            <x-input-error for="url" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="emoji" value="{!! __('guidelines.emoji_label') !!}" />
            <x-input id="emoji"
                         type="text"
                         class="mt-1 block w-full"
                         wire:model="emoji"
                         autocomplete="off"
                         aria-autocomplete="none"
                         @error('emoji') aria-invalid="true" aria-describedby="emoji-error" @enderror />
            <x-input-error for="emoji" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>