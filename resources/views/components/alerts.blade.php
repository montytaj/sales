<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showSystemToast === 'function') {
            @if (session('success'))
                window.showSystemToast('{{ __("general.success") ?? "نجاح" }}', @json(session('success')), 'success');
            @endif

            @if (session('error'))
                window.showSystemToast('{{ __("general.error") ?? "خطأ" }}', @json(session('error')), 'danger');
            @endif

            @if (session('warning'))
                window.showSystemToast('{{ __("general.warning") ?? "تنبيه" }}', @json(session('warning')), 'warning');
            @endif

            @if (session('info'))
                window.showSystemToast('{{ __("general.info") ?? "إشعار" }}', @json(session('info')), 'info');
            @endif

            @if (isset($errors) && $errors->any())
                @foreach ($errors->all() as $err)
                    window.showSystemToast('{{ __("general.error") ?? "خطأ في المدخلات" }}', @json($err), 'danger');
                @endforeach
            @endif
        }
    });
</script>
