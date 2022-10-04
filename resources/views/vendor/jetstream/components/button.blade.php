<button {{ $attributes->merge(['type' => 'submit', 'class' => 'button primary-button-red']) }}>
    {{ $slot }}
</button>
