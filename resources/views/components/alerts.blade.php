@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm rounded-lg d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
        <div>
            <strong>{{ __('general.success') }}:</strong> {{ session('success') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('success');
            }
        });
    </script>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm rounded-lg d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
        <div>
            <strong>{{ __('general.error') }}:</strong> {{ session('error') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('warning');
            }
        });
    </script>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4 border-0 shadow-sm rounded-lg d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
        <div>
            <strong>{{ __('general.warning') }}:</strong> {{ session('warning') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('warning');
            }
        });
    </script>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4 border-0 shadow-sm rounded-lg d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
        <div>
            <strong>{{ __('general.info') }}:</strong> {{ session('info') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('notification');
            }
        });
    </script>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm rounded-lg" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-x-circle-fill fs-5 me-2 text-danger"></i>
            <strong>{{ __('general.error') }}:</strong>
        </div>
        <ul class="mb-0 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('warning');
            }
        });
    </script>
@endif
