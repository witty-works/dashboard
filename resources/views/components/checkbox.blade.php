@php
    $error = $attributes->get('error');
    $attributes = $attributes->except('error');
    if ($error && isset($errors) && $errors->has($error)) {
        // Marks the field and points at the message, which the form renders
        // with the id "<field>-error" (x-input-error, x-validation-errors).
        $attributes = $attributes->merge(['aria-invalid' => 'true', 'aria-describedby' => str_replace('.', '_', $error) . '-error']);
    }
@endphp
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
