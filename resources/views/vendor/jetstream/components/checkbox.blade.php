<label class="switch">
    <input type="checkbox" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round"></span>
</label>
@if($disabled)
<div class="p-3 whitespace-nowrap">
@include('partials.witty-teams-only')
</div>
@endif
<div class="guidelines-form-section-label">{{ $label }}</div>
