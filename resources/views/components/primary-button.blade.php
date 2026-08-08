<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary-custom shadow-sm font-semibold transition']) }}>
    {{ $slot }}
</button>

