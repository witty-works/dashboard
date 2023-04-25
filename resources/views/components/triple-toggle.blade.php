<label>
    <input type="radio" value="0" {{ empty($disabled) ? '' : 'disabled' }} {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <input type="radio" value="1" {{ empty($disabled) ? '' : 'disabled' }}  {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
    <input type="radio" value="2" {{ empty($disabled) ? '' : 'disabled' }}  {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
</label>

<div class="lato-small-text-p">{!! $label !!}</div>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])
