<label>
    <div class="switch">
        <input type="checkbox" 
               {{ empty($disabled) ? '' : 'disabled' }} 
               {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!}
               aria-disabled="{{ empty($disabled) ? 'false' : 'true' }}" />
        <span class="slider round {{ empty($disabled) ? '' : 'disabled' }}"></span>
    </div>
    <span class="lato-small-text-p">{!! $label !!}</span>
</label>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])
