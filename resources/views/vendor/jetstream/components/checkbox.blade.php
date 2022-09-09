<label class="switch">
    <input type="checkbox" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round"></span>
</label>
@if(!empty($disabled))
<div class="p-3 whitespace-nowrap">
@if($disabled === 'locked')
    @include('partials.locked')
@else
    @include('partials.witty-teams-only')
@endif
</div>
@endif
<div class="guidelines-form-section-label">{{ $label }}</div>
