<x-jet-form-section submit="updateOrganizationGuidelinesOrthography">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_orthography')) !!}
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesOrthography">
        <div class="col-span-6 sm:col-span-4">
            @foreach(\App\Models\OrganizationGuidelines::DISABLED_CATEGORIES_ORTHOGRAPHY as $category)
            {{ __('guidelines.enable_' . $category ) }}
            <x-jet-input id="disabled_categories_{{ $category }}"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="disabled_categories_{{ $category }}"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            @endforeach
        </div>
        <x-jet-input id="disabled_categories_force_orthography"
            value="1"
            type="checkbox"
            class="guidelines-form-section-toggle"
            wire:model.defer="disabled_categories_force_orthography"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />  
        <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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