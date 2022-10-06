@props(['disabled' => false, 'options' => []])

<select {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm border-radius lato-small-text-p']) !!}>
    @foreach ($options as $key => $label)
        <option {{ $disabled ? 'disabled' : '' }} value="{{ is_int($key) ? $label : $key }}" wire:key="{{ is_int($key) ? $label : $key }}">{{ is_int($key) ? $label : __($label) }}</option>
    @endforeach
</select>
