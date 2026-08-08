<label @if (!empty($title)) title="{{ $title }}" @endif>
    <div class="switch">
        <input type="checkbox"
            {{ empty($enabled) ? '' : 'checked="true"' }}
            {{ empty($disabled) ? '' : 'disabled' }}
            {!! $attributes->merge(['class' => 'guidelines-form-section-toggle']) !!} />
        <span class="slider round {{ empty($disabled) ? '' : 'disabled' }}"></span>
    </div>
    <span class="lato-small-text-p">{!! $label !!}</span>
</label>
@include('partials.toggle_label', ['disabled' => $disabled ?? false])
