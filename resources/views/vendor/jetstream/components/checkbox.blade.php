<label class="switch">
    <input type="checkbox" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <span class="slider round {{ empty($disabled) ? '' : 'disabled' }}"></span>
</label>

<div class="lato-small-text-p">{!! $label !!}</div>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])
