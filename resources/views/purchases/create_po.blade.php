<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('purchases.index'), 'label' => __('financial.purchases_title')],
                ['label' => 'إصدار أمر شراء جديد']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-cart-check-fill text-success me-2"></i>إصدار أمر شراء جديد
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('purchases.store-po') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="supplier_id" class="form-label font-semibold">المورد <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="branch_id" class="form-label font-semibold">الفرع طالب الشراء</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">-- اختر الفرع --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="total_amount" class="form-label font-semibold">المبلغ الإجمالي <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="total_amount" id="total_amount" class="form-control" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tax_amount" class="form-label font-semibold">الضريبة ({{ setting('tax_percentage', 15.00) }}%)</label>
                                <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" class="form-control" value="0">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="order_date" class="form-label font-semibold">تاريخ الطلب <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" id="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label font-semibold">تفاصيل وملاحظات الأصناف المطلوبة</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="تفاصيل خامات الأخشاب، المواد، الكميات..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('purchases.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ وإصدار أمر الشراء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
