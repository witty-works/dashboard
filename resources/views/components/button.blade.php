@php $class = $attributes->get('secondary') ? 'secondary-button-red' : 'primary-button-red'; @endphp
<button {{ $attributes->merge(['class' => 'button '.$class]) }}>
    {{ $slot }}
</button>
