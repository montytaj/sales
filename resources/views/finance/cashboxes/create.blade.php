<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('cashboxes.index'), 'label' => __('payments.cashboxes_list')],
                ['label' => __('payments.create_cashbox')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-safe2 text-primary me-2"></i>{{ __('payments.create_cashbox') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('cashboxes.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="name_ar" class="form-label font-semibold">{{ __('payments.cashbox_name') }} (بالعربية) <span class="text-danger">*</span></label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
                                @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="name_en" class="form-label font-semibold">{{ __('payments.cashbox_name') }} (English)</label>
                                <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en') }}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="branch_id" class="form-label font-semibold">الفرع المرتبط</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">-- كافة الفروع / الخزنة الرئيسية --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="opening_balance" class="form-label font-semibold">{{ __('payments.opening_balance') }} ({{ setting('currency', 'SDG') }}) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror" value="{{ old('opening_balance', '0.00') }}" required>
                                @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-semibold d-block">{{ __('payments.authorized_users') }}</label>
                            <div class="row g-2 max-h-48 overflow-y-auto p-2 border rounded">
                                @foreach ($users as $u)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="users[]" value="{{ $u->id }}" id="user_{{ $u->id }}">
                                            <label class="form-check-label" for="user_{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('cashboxes.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ الخزنة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
