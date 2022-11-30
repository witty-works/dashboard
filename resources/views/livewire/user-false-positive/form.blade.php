<x-jet-form-section submit="storeFalsePositive">
    <x-slot name="title">
        {{ __('guidelines.create_false_positive') }}
    </x-slot>

    <x-slot name="description">
        @if($user->getFalsePositivesLimitReached() && !$user->subscribed())
            {!! __('guidelines.false_positive_limit_reached', ['max_count' => $user->getFalsePositivesCount(), 'url' => route('teams.subscription')]) !!}
        @else
            {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}
        @endif
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />

            <x-jet-input id="false_positive"
                type="textarea"
                class="mt-1 block w-full textarea-as-input"
                wire:model.defer="false_positive"
                autocomplete="false_positive"
            />

            <x-jet-input-error for="false_positive" class="mt-2" />
        </div>
{{--
        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />

            <x-select id="language_code"
                :options="\App\Models\FalsePositive::LANGUAGE_CODE"
                class="mt-1 block w-full"
                wire:model.defer="language_code"
            />

                <x-jet-input-error for="language_code" class="mt-2" />
        </div>
--}}
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>