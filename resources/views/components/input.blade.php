@props(['disabled' => false])
@php
    $attributes = $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm border-radius']);
@endphp
@if($attributes->get('type') === 'textarea')
<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>{{ $attributes->get('value') }}</textarea>
@else
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes !!}>
@endif