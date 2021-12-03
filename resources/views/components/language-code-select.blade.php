@props(['disabled' => false, 'options' => ['' => __('content.any'), 'de' => 'de', 'en' => 'en']])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm']) !!}>
    @foreach ($options as $key => $label)
        <option value="{{  $key }}">{{ $label }}</option>
    @endforeach
</select>
