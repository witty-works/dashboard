<x-jet-form-section submit="storeFalsePositive">
    <x-slot name="title">
        <h2 id="organization_false_positives">{{ __('guidelines.create_false_positive') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p aria-describedby="organization_false_positives">
            @if($model->getFalsePositivesLimitReached() && Auth::user()->ownsTeam($model) && !$model->subscribed())
                {!! __('guidelines.false_positive_limit_reached', ['max_count' => $model->getFalsePositivesCount(), 'url' => route('teams.subscription')]) !!}
            @else
                {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}
            @endif
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label id="false_positive_label" for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />
            <x-jet-input id="false_positive"
                         type="textarea"
                         class="mt-1 block w-full textarea-as-input"
                         wire:model.defer="false_positive"
                         autocomplete="false_positive"
                         :disabled="! Auth::user()->hasTeamPermission($model, 'edit_guidelines')"
                         aria-describedby="false_positive_label" />
            <x-jet-input-error for="false_positive" class="mt-2" />
            
            @unless(Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
                <p id="false_positive_help" class="text-sm text-red-600">
                    You do not have permission to edit this field.
                </p>
            @endunless
        </div>
{{--
        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-jet-label for="language_code" value="{!! __('guidelines.language_code_label') !!}" />

            <x-select id="language_code"
                :options="\App\Models\FalsePositive::LANGUAGE_CODES"
                class="mt-1 block w-full"
                wire:model.defer="language_code"
                :disabled="! Auth::user()->hasTeamPermission($model, 'edit_guidelines')"
            />

                <x-jet-input-error for="language_code" class="mt-2" />
        </div>
--}}
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-jet-form-section>