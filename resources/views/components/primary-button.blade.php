<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn']) }}>
    {{ $slot }}
</button>