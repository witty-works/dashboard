<label class="switch">
    <input type="checkbox" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round"></span>
</label>

<div class="lato-small-text-p">{{ $label }}</div>
@if(!empty($disabled))
<div class="p-3 whitespace-nowrap">
@if($disabled === 'locked')
    @include('partials.locked')
@else
    @include('partials.witty-teams-only')
@endif
</div>
@endif
