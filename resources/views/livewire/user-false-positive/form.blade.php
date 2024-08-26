<x-form-section submit="storeFalsePositive">
    <x-slot name="title">
        <h2 id="false_positives">{{ __('guidelines.create_false_positive') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p aria-describedby="false_positives">
            @if($model->getFalsePositivesLimitReached() && !$model->isPremium())
                {!! __('guidelines.false_positive_limit_reached', ['max_count' => $model->getFalsePositivesCount(), 'url' => route('teams.subscription')]) !!}
            @else
                {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}
            @endif
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-label for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />
            
            <x-input id="false_positive"
                         type="textarea"
                         class="mt-1 block w-full textarea-as-input"
                         wire:model="false_positive"
                         autocomplete="false_positive"
            />
            <x-input-error for="false_positive" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>