<x-jet-form-section submit="updateOrganizationGuidelines">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines') }}
    </x-slot>

    <x-slot name="description">
        {{ __('guidelines.manage_organization_guidelines_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="german_gender_ending" value="{{ __('guidelines.german_gender_ending') }}" />

            <x-select id="german_gender_ending"
                :options="\App\Models\OrganizationGuidelines::GERMAN_GENDER_ENDING"
                class="mt-1 block w-full"
                wire:model.defer="german_gender_ending"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

                <x-jet-input-error for="german_gender_ending" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="gendered_roles_format" value="{{ __('guidelines.gendered_roles_format') }}" />

            <x-select id="gendered_roles_format"
                :options="\App\Models\OrganizationGuidelines::GENDERED_ROLES_FORMAT"
                class="mt-1 block w-full"
                wire:model.defer="gendered_roles_format"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="store_context" value="{{ __('guidelines.store_context') }}" />

            <x-jet-input id="store_context"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="store_context"
                        :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="store_context" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="expert_mode" value="{{ __('guidelines.expert_mode') }}" />

            <x-jet-input id="expert_mode"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="expert_mode"
                        :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="expert_mode" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="singular_they" value="{{ __('guidelines.singular_they') }}" />

            <x-jet-input id="singular_they"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="singular_they"
                        :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="singular_they" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="preferred_variants" value="{{ __('guidelines.preferred_variants') }}" />

            <x-jet-label for="preferred_variants_en" value="{{ __('guidelines.preferred_variants_en') }}" />

            <x-select id="preferred_variants_en"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_EN"
                class="mt-1 block w-full"
                wire:model.defer="preferred_variants_en"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
            <x-jet-label for="preferred_variants_de" value="{{ __('guidelines.preferred_variants_de') }}" />

            <x-select id="preferred_variants_de"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_DE"
                class="mt-1 block w-full"
                wire:model.defer="preferred_variants_de"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="preferred_variants_de" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="disabled_categories" value="{{ __('guidelines.disabled_categories') }}" />

            @foreach(\App\Models\OrganizationGuidelines::DISABLED_CATEGORIES as $category)
                <x-jet-label for="disabled_categories_{{ $category }}" value="{{ __('guidelines.disabled_categories_'.$category) }}" />

                <x-jet-input id="disabled_categories_{{ $category }}"
                            value="1"
                            type="checkbox"
                            class="mt-1 block"
                            wire:model.defer="disabled_categories_{{ $category }}"
                            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            @endforeach

        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('content.saved') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>