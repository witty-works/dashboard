<x-form-section class="py-10" submit="updateLanguageGuidelinesGenericMasculine" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_generic_masculine') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_generic_masculine')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGenericMasculine">
        @php
            $disabled = !$model->isPremium();
        @endphp

        @foreach (\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT as $key => $value)
        <div class="margin-bottom">
            <label class="guidelines-form-section-radio" style="cursor: {{ $disabled ? 'not-allowed' : 'pointer' }};">
                <input
                    type="radio"
                    id="gendered_roles_format_{{ $key }}"
                    name="gendered_roles_format"
                    value="{{ $key }}"
                    wire:model="gendered_roles_format"
                    style="cursor: {{ $disabled ? 'not-allowed' : 'pointer' }};"
                    @if($disabled) disabled @endif
                >
                    {!! __($value) !!}
            </label>
        </div>
        @endforeach

        <x-input-error for="gendered_roles_format" class="mt-2" />
    </x-slot>

    @if($model->isPremium())
    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-checkbox
                id="generic_masculine_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model="generic_masculine_force"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
    </x-slot>
    @endif

</x-form-section>
