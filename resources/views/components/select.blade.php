@props(['disabled' => false, 'options' => [], 'selected' => false])

<select {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm border-radius lato-small-text-p']) !!}>
    @foreach ($options as $key => $label)
        @php
            $isKeyInteger = is_int($key);
            $optionValue = $isKeyInteger ? $label : $key;
            $optionText = $isKeyInteger ? $label : __($label);
            $isDisabled = $disabled || !empty($disabled[$key]);
            $isSelected = $selected === $key;
        @endphp

        <option
            @if($isDisabled) disabled @endif
            value="{{ $optionValue }}"
            wire:key="{{ $optionValue }}"
            @if($isSelected) selected @endif
        >
            {{ $optionText }}
        </option>
    @endforeach
</select>
