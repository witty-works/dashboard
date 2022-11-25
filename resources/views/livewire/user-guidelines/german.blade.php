<x-jet-form-section class="max-w-7xl mx-auto py-10" submit="updateLanguageGuidelinesGerman" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">
        <div class="margin-bottom">{!! __('guidelines.gendered_roles_format') !!}</div>

        <div class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}</div>

        <div class="flex flex-row">
            <div>
                <x-select id="gendered_roles_format"
                    :options="\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT"
                    class="guidelines-form-section-dropdown margin-bottom"
                    wire:model.defer="gendered_roles_format"
                    :disabled="!$user->subscribed() || \App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'german_rules')"
                />

                <x-jet-input-error for="gendered_roles_format" class="mt-2" />
            </div>

            @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'german_rules'))
              @include('partials.locked')
            @elseif(!$user->subscribed())
              <div class="p-3">
                @include('partials.witty-teams-only')
              </div>
            @endif

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
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'german_rules')"
                />

                <x-jet-input-error for="german_gender_ending" class="mt-2" />
            </div>
            @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'german_rules'))
              @include('partials.locked')
            @endif
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'german_rules'))
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
