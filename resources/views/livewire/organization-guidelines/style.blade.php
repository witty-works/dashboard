<x-jet-form-section submit="updateOrganizationGuidelinesStyle">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inclusive_and_style') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_style')) !!}
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesStyle">
        <div class="col-span-6 sm:col-span-4">
            @foreach(\App\Models\OrganizationGuidelines::DISABLED_CATEGORIES_STYLE as $category)            
            {{ __('guidelines.enable_' . $category ) }}

            <x-jet-input id="disabled_categories_{{ $category }}"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="disabled_categories_{{ $category }}"
                wire:change="updateOrganizationGuidelinesStyle()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            @endforeach

            @foreach(\App\Models\OrganizationGuidelines::DISABLED_CATEGORIES_INCLUSIVE as $category)
            {{ __('guidelines.enable_' . $category ) }}

            <x-jet-input id="disabled_categories_{{ $category }}"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="disabled_categories_{{ $category }}"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            @endforeach
        </div>

        <x-jet-input 
            id="disabled_categories_force_style"
            value="1"
            type="checkbox"
            class="guidelines-form-section-toggle"
            wire:model.defer="disabled_categories_force_style"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
        <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
    </x-slot>
    
    <x-slot name="save">
       <!-- TODO: make sure this includes both style and inclusive -->
       <div class="guidelines-form-section">     
            <x-jet-input 
                id="disabled_categories_force_{{ $category }}"
                value="1"
                type="checkbox"
                class="guidelines-form-section-toggle"
                wire:model.defer="disabled_categories_force_{{ $category }}"
                wire:change="updateOrganizationGuidelinesInclusive()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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