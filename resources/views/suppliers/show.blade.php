<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('suppliers.index'), 'label' => __('suppliers.suppliers_list')],
                ['label' => __('suppliers.show_supplier')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-truck text-primary me-2"></i>{{ __('suppliers.show_supplier') }}: {{ $supplier->name }}
            </h2>
            @can('edit-suppliers')
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i> {{ __('suppliers.edit_supplier') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Supplier Info Card -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                        <i class="bi bi-truck fs-1"></i>
                    </div>
                    <h4 class="font-bold text-dark mb-1">{{ $supplier->name }}</h4>
                    @if ($supplier->company_name)
                        <p class="text-muted mb-2 font-semibold">{{ $supplier->company_name }}</p>
                    @endif
                    <p class="text-muted mb-2"><code>{{ $supplier->code }}</code></p>

                    <div class="mb-3">
                        <span class="text-warning d-block mb-2 fs-5">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $supplier->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </span>
                        @if ($supplier->is_active)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">{{ __('suppliers.active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6">{{ __('suppliers.inactive') }}</span>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="text-start small">
                        <p class="mb-2"><strong><i class="bi bi-person me-1"></i>{{ __('suppliers.contact_person') }}:</strong> {{ $supplier->contact_person ?? '-' }}</p>
                        <p class="mb-2"><strong><i class="bi bi-telephone me-1"></i>{{ __('suppliers.phone') }}:</strong> {{ $supplier->phone }}</p>
                        <p class="mb-2"><strong><i class="bi bi-envelope me-1"></i>{{ __('suppliers.email') }}:</strong> {{ $supplier->email ?? '-' }}</p>
                        <p class="mb-2"><strong><i class="bi bi-geo-alt me-1"></i>{{ __('suppliers.address') }}:</strong> {{ $supplier->address ?? '-' }} ({{ $supplier->city }})</p>
                        <p class="mb-2"><strong><i class="bi bi-file-earmark-text me-1"></i>{{ __('suppliers.cr_number') }}:</strong> {{ $supplier->cr_number ?? '-' }}</p>
                        <p class="mb-0"><strong><i class="bi bi-receipt me-1"></i>{{ __('suppliers.vat_number') }}:</strong> {{ $supplier->vat_number ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services & Attachments Column -->
        <div class="col-12 col-md-7 col-lg-8">
            <!-- Services Provided Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 font-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-boxes text-info"></i>{{ __('suppliers.services_provided') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-dark fs-6">{{ $supplier->services_provided ?? 'لم تنصس أي مواد أو خدمات محددة.' }}</p>
                </div>
            </div>

            <!-- Notes Card -->
            @if ($supplier->notes)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-sticky text-warning me-2"></i>{{ __('suppliers.notes') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0 text-dark">{{ $supplier->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Attachments Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-paperclip text-primary me-2"></i>{{ __('suppliers.attachments') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#uploadForm">
                        <i class="bi bi-upload me-1"></i>رفع مرفق جديد
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="collapse mb-3" id="uploadForm">
                        <form method="POST" action="{{ route('suppliers.upload-attachment', $supplier) }}" enctype="multipart/form-data" class="p-3 bg-light rounded border">
                            @csrf
                            <div class="mb-3">
                                <label for="attachmentFile" class="form-label font-semibold">اختر ملف العقد أو الاعتماد</label>
                                <input type="file" name="attachment" id="attachmentFile" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-cloud-upload me-1"></i>رفع الملف</button>
                        </form>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($supplier->attachments as $att)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                                    <div>
                                        <h6 class="mb-0 font-semibold text-dark">{{ $att->file_name }}</h6>
                                        <small class="text-muted">{{ round($att->file_size / 1024, 2) }} KB | بواسطة: {{ $att->uploader?->name ?? 'النظام' }}</small>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-download"></i></a>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">لا توجد مرفقات مسجلة لهذا المورد حالياً.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
