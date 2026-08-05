<x-form-section submit="storeFalsePositive">
    <x-slot name="title">
        <h2 id="organization_false_positives">{{ __('guidelines.create_false_positive') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p aria-describedby="organization_false_positives">
                {!! Str::markdown(__('guidelines.create_new_false_positive_description')) !!}
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-label id="false_positive_label" for="false_positive" value="{!! __('guidelines.false_positive_label') !!}" />
            <x-input id="false_positive"
                         type="textarea"
                         class="mt-1 block w-full textarea-as-input"
                         wire:model="false_positive"
                         autocomplete="off"
                         aria-autocomplete="none"
                         :disabled="! Auth::user()->hasTeamPermission($model, 'edit_guidelines')"
            />
            <x-input-error for="false_positive" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>