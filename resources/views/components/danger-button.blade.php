<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger-custom shadow-sm font-semibold transition']) }}>
    {{ $slot }}
</button>

