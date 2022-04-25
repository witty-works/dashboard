<x-jet-form-section submit="updateOrganizationGuidelinesGerman">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}

        @if(! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed())
            {!! __('guidelines.gendered_roles_format_subscription_required', ['url' => route('stripe.portal')]) !!}
        @endif
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesGerman">  
        <div class="guidelines-form-title">{!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}</div>

        <div class="guidelines-form-section-dropdown-label"> {{ __('guidelines.german_gender_ending') }}</div>
        <x-select id="german_gender_ending"
            :options="\App\Models\OrganizationGuidelines::GERMAN_GENDER_ENDING"
            class="guidelines-form-section-dropdown"
            wire:model.defer="german_gender_ending"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
        <x-jet-input-error for="german_gender_ending" class="mt-2" />

        <div class="guidelines-form-section-dropdown-label"> {{ __('guidelines.gendered_roles_format') }}</div>
        <x-select id="gendered_roles_format"
            :options="\App\Models\OrganizationGuidelines::GENDERED_ROLES_FORMAT"
            class="guidelines-form-section-dropdown"
            wire:model.defer="gendered_roles_format"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed()" />

        <x-jet-input-error for="gendered_roles_format" class="mt-2" />

        <div class="guidelines-form-section--apply-for-all">
            <label class="switch">
                <input
                    id="german_rules_force"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="german_rules_force"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')">
                <span class="slider round"></span>
            </label>
            <div class="guidelines-form-section-label--apply-for-all">{{ __('guidelines.set_for_all') }}</div>
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>