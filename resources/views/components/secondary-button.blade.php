<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary-custom font-medium transition']) }}>
    {{ $slot }}
</button>

