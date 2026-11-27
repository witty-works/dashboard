@props(['disabled' => false, 'error' => null])
@php
    if ($error && isset($errors) && $errors->has($error)) {
        // Marks the field and points at the message, which the form renders
        // with the id "<field>-error" (x-input-error, x-validation-errors).
        $attributes = $attributes->merge(['aria-invalid' => 'true', 'aria-describedby' => str_replace('.', '_', $error) . '-error']);
    }
    $attributes = $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm border-radius']);
@endphp
@if($attributes->get('type') === 'textarea')
<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>{{ $attributes->get('value') }}</textarea>
@else
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>
@endif