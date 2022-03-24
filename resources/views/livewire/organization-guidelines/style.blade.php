<x-jet-form-section submit="updateOrganizationGuidelinesStyle">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_style') }}
    </x-slot>

    <x-slot name="description">
        {{ __('guidelines.manage_organization_guidelines_description_style') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="disabled_categories" value="{{ __('guidelines.disabled_categories') }}" />

            @foreach(\App\Models\OrganizationGuidelines::DISABLED_CATEGORIES_STYLE as $category)
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