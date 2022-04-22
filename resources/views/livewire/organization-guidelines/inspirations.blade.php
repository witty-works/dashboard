<x-jet-form-section submit="updateOrganizationGuidelinesInspirations">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesInspirations">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_inspiration')) !!}</div>
        <div class="guidelines-form-section">
            <label class="switch">
                <input
                    id="show_inspiration_alternatives"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="show_inspiration_alternatives"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed()">
                <span class="slider round"></span>
            </label>
            <div class="guidelines-form-section-label--apply-for-all">{{ __('guidelines.enable_show_inspiration_alternatives') }}</div>
        </div>

        <div class="guidelines-form-section--apply-for-all">
            <label class="switch">
                <input
                    id="show_inspiration_alternatives_force"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="show_inspiration_alternatives_force"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed()" 
                >
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