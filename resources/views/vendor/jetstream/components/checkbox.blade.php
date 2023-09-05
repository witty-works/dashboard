<div class="switch">
    <input type="checkbox"
        {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!}
        {{ empty($disabled) ? '' : 'disabled' }} 
        aria-disabled="{{ empty($disabled) ? 'false' : 'true' }}" />
    <span class="slider round {{ empty($disabled) ? '' : 'disabled' }}"></span>
</div>
<label class="lato-small-text-p" for="{{ $attributes->get("id") }}">
    {!! $label !!}
    @include('partials.toggle_label', ['disabled' => $disabled ?? false])
</label>