<x-form-section class="py-10" submit="updateLanguageGuidelinesGenericMasculine" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_generic_masculine') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_generic_masculine')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGenericMasculine">
        @php
            $disabled = !$model->isPremium() || \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'generic_masculine');
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
                    @if(!$model->isPremium())
                        <span class="p-3">
                            @include('partials.witty-teams-only')
                        </span>
                    @endif
                    @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'generic_masculine')])
                </label>
        </div>
        @endforeach

        <x-input-error for="gendered_roles_format" class="mt-2" />
    </x-slot>

    @if(!$disabled)
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>
