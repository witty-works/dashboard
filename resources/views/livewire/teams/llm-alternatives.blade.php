<x-form-section submit="updateTeamsLlmAlternatives">
    <x-slot name="title">
        {{ __('guidelines.llm_alternatives_headline') }}
        @include('partials.beta')
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.llm_alternatives_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-checkbox
                id="llm_alternatives"
                value="1"
                wire:model="llm_alternatives"
                :label="__('guidelines.llm_alternatives')"
                :disabled="!$model->isPremium() ? 'upgrade' : (Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked')"
                aria-label="{{ __('guidelines.llm_alternatives') }}"
            />

            <x-input-error for="llm_alternatives" class="mt-2" aria-describedby="llm_alternatives" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'update') && $model->isPremium())
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
