<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('customers.index'), 'label' => __('customers.customers_list')],
                ['label' => __('customers.show_customer')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-person-vcard text-primary me-2"></i>{{ __('customers.show_customer') }}: {{ $customer->name }}
            </h2>
            @can('edit-customers')
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i> {{ __('customers.edit_customer') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Customer Info Card -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                        <i class="bi bi-person-vcard fs-1"></i>
                    </div>
                    <h4 class="font-bold text-dark mb-1">{{ $customer->name }}</h4>
                    @if ($customer->company_name)
                        <p class="text-muted mb-2 font-semibold">{{ $customer->company_name }}</p>
                    @endif
                    <p class="text-muted mb-2"><code>{{ $customer->code }}</code></p>

                    <div class="mb-3">
                        <span class="badge bg-info-subtle text-info border border-info px-3 py-2 fs-6">
                            {{ __('customers.' . $customer->category) }}
                        </span>
                        @if ($customer->is_active)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 ms-1">{{ __('customers.active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6 ms-1">{{ __('customers.inactive') }}</span>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="text-start small">
                        <p class="mb-2"><strong><i class="bi bi-telephone me-1"></i>{{ __('customers.phone') }}:</strong> {{ $customer->phone }}</p>
                        @if ($customer->phone_secondary)
                            <p class="mb-2"><strong><i class="bi bi-telephone-plus me-1"></i>{{ __('customers.phone_secondary') }}:</strong> {{ $customer->phone_secondary }}</p>
                        @endif
                        <p class="mb-2"><strong><i class="bi bi-envelope me-1"></i>{{ __('customers.email') }}:</strong> {{ $customer->email ?? '-' }}</p>
                        <p class="mb-2"><strong><i class="bi bi-geo-alt me-1"></i>{{ __('customers.address') }}:</strong> {{ $customer->address ?? '-' }} ({{ $customer->city }})</p>
                        <p class="mb-2"><strong><i class="bi bi-file-earmark-text me-1"></i>{{ __('customers.cr_number') }}:</strong> {{ $customer->cr_number ?? '-' }}</p>
                        <p class="mb-0"><strong><i class="bi bi-receipt me-1"></i>{{ __('customers.vat_number') }}:</strong> {{ $customer->vat_number ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial & Attachments Column -->
        <div class="col-12 col-md-7 col-lg-8">
            <!-- Credit Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 font-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-cash-stack text-success"></i>الحد الائتماني وشروط الدفع
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <span class="text-muted d-block mb-1">{{ __('customers.credit_limit') }}</span>
                            <h3 class="font-bold text-success mb-0">{{ number_format($customer->credit_limit, 2) }} {{ setting('currency', 'SDG') }}</h3>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block mb-1">{{ __('customers.credit_period_days') }}</span>
                            <h3 class="font-bold text-dark mb-0">{{ $customer->credit_period_days }} يوم</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            @if ($customer->notes)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-sticky text-warning me-2"></i>{{ __('customers.notes') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0 text-dark">{{ $customer->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Attachments Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-paperclip text-primary me-2"></i>{{ __('customers.attachments') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#uploadForm">
                        <i class="bi bi-upload me-1"></i>{{ __('customers.upload_attachment') }}
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="collapse mb-3" id="uploadForm">
                        <form method="POST" action="{{ route('customers.upload-attachment', $customer) }}" enctype="multipart/form-data" class="p-3 bg-light rounded border">
                            @csrf
                            <div class="mb-3">
                                <label for="attachmentFile" class="form-label font-semibold">اختر الملف من جهازك</label>
                                <input type="file" name="attachment" id="attachmentFile" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-cloud-upload me-1"></i>رفع الملف</button>
                        </form>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($customer->attachments as $att)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                                    <div>
                                        <h6 class="mb-0 font-semibold text-dark">{{ $att->file_name }}</h6>
                                        <small class="text-muted">{{ round($att->file_size / 1024, 2) }} KB | رفع بواسطة: {{ $att->uploader?->name ?? 'النظام' }}</small>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-download"></i></a>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">لا توجد مرفقات مسجلة لهذا العميل حالياً.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
