<x-jet-form-section submit="updateLanguageGuidelinesGerman">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">  
        <div class="margin-bottom">
            {!! __('guidelines.gendered_roles_format') !!}
        </div>


        <div class="lato-small-text-p"> {{ __('guidelines.german_gender_ending') }}</div>
        <x-select id="german_gender_ending"
            :options="\App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING"
            class="guidelines-form-section-dropdown lato-small-text-p margin-bottom"
            wire:model.defer="german_gender_ending"
        />

        <div class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}</div>
        <div class="flex flex-row">
            <div>
                <x-select id="gendered_roles_format"
                    :options="\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT"
                    class="guidelines-form-section-dropdown margin-bottom"
                    wire:model.defer="gendered_roles_format"
                    :disabled="!$team->subscribed()"
                />

                <x-jet-input-error for="gendered_roles_format" class="mt-2" />
            </div>
            <div class="p-3">
                @if(!$team->subscribed())
                @include('partials.witty-teams-only')
                @endif
            </div>
        </div>

        <div class="margin-bottom">
            {!! __('guidelines.german_gender_ending') !!}
        </div>

        <div class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_gender_ending_sub_title') !!}</div>
        <div class="flex flex-row">
            <div>
                <x-select id="german_gender_ending"
                    :options="\App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING"
                    class="guidelines-form-section-dropdown"
                    wire:model.defer="german_gender_ending"
                />

                <x-jet-input-error for="german_gender_ending" class="mt-2" />
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="german_rules_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="german_rules_force"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
        @endif
    </x-slot>

</x-jet-form-section>
