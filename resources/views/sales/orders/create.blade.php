<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('customer-orders.index'), 'label' => __('sales.orders_list')],
                ['label' => __('sales.create_order')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-inboxes-fill text-primary me-2"></i>{{ __('sales.create_order') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('customer-orders.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">-- اختر العميل --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="branch_id" class="form-label font-semibold">الفرع</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">-- الفرع الرئيسي --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="requirements_summary" class="form-label font-semibold">{{ __('sales.requirements_summary') }} <span class="text-danger">*</span></label>
                            <textarea name="requirements_summary" id="requirements_summary" rows="4" class="form-control @error('requirements_summary') is-invalid @enderror" placeholder="تفاصيل طلب العميل، المقاسات، الألوان، والمواصفات المطلوبة..." required>{{ old('requirements_summary') }}</textarea>
                            @error('requirements_summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label font-semibold">ملاحظات داخلية</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customer-orders.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ الطلب
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
