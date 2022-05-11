<label class="switch">
    <input type="checkbox" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round"></span>
</label>
@if($disabled)
    <img src="{{ asset('svg/options-lock.svg') }}" alt="{{ __('teams.upgrade_to_witty_teams') }}" title="{{ __('teams.upgrade_to_witty_teams') }}" class="guidelines-form-section-lock p-2" />
@endif
<div class="guidelines-form-section-label">{{ $label }}</div>
