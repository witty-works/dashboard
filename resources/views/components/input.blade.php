@props(['disabled' => false, 'error' => null])
@php
    if ($error && isset($errors) && $errors->has($error)) {
        // Marks the field and points at the message, which the form renders
        // with the id "<field>-error" (x-input-error, x-validation-errors).
        $attributes = $attributes->merge(['aria-invalid' => 'true', 'aria-describedby' => str_replace('.', '_', $error) . '-error']);
    }
    $attributes = $attributes->merge(['class' => 'border-gray-500 focus:border-indigo-700 focus:ring-2 focus:ring-indigo-700 rounded-md shadow-sm border-radius']);
@endphp
@if($attributes->get('type') === 'textarea')
<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>{{ $attributes->get('value') }}</textarea>
@else
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>
@endif