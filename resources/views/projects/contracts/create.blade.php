<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('contracts.index'), 'label' => __('projects.contracts')],
                ['label' => __('projects.create_contract')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>{{ __('projects.create_contract') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('contracts.store') }}" id="contractForm">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">-- اختر العميل --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="branch_id" class="form-label font-semibold">الفرع المسؤول</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">-- اختر الفرع --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="scope_of_work" class="form-label font-semibold">نطاق العمل والتفاصيل الفنية <span class="text-danger">*</span></label>
                            <textarea name="scope_of_work" id="scope_of_work" rows="3" class="form-control" required placeholder="تحديد بنود العقد والتوريدات ومواصفات المشروع والواجهات..."></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="total_amount" class="form-label font-semibold">المبلغ الإجمالي <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="total_amount" id="total_amount" class="form-control" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="discount_amount" class="form-label font-semibold">الخصم</label>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" class="form-control" value="0">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="tax_amount" class="form-label font-semibold">ضريبة القيمة المضافة ({{ setting('tax_percentage', 15.00) }}%)</label>
                                <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" class="form-control" value="0">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="start_date" class="form-label font-semibold">تاريخ بداية العقد <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Payment Milestones Schedule -->
                        <h6 class="font-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-calendar-check me-2"></i>خطة وجدولة الدفعات (غير محدودة)</h6>

                        <div id="milestonesContainer">
                            <div class="row g-2 mb-2 align-items-center milestone-row">
                                <div class="col-md-4">
                                    <input type="text" name="milestones[0][milestone_name]" class="form-control" placeholder="اسم الدفعة / المرحلة (مثل: الدفعة المقدمة عند التوقيع)" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="milestones[0][due_date]" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="milestones[0][amount_type]" class="form-select">
                                        <option value="percentage">نسبة مئوية (%)</option>
                                        <option value="fixed">مبلغ ثابت ({{ setting('currency', 'SDG') }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" name="milestones[0][value]" class="form-control" placeholder="القيمة" value="30" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-row" disabled><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="addMilestoneBtn" class="btn btn-sm btn-outline-primary mb-4">
                            <i class="bi bi-plus-circle me-1"></i>إضافة دفعة إضافية
                        </button>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('contracts.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ العقد والجدولة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIdx = 1;
            const container = document.getElementById('milestonesContainer');
            const addBtn = document.getElementById('addMilestoneBtn');

            addBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 align-items-center milestone-row';
                row.innerHTML = `
                    <div class="col-md-4">
                        <input type="text" name="milestones[${rowIdx}][milestone_name]" class="form-control" placeholder="اسم الدفعة / المرحلة" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="milestones[${rowIdx}][due_date]" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-2">
                        <select name="milestones[${rowIdx}][amount_type]" class="form-select">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت ({{ setting('currency', 'SDG') }})</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="milestones[${rowIdx}][value]" class="form-control" placeholder="القيمة" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-row"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                container.appendChild(row);
                rowIdx++;
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-row')) {
                    const row = e.target.closest('.milestone-row');
                    if (container.querySelectorAll('.milestone-row').length > 1) {
                        row.remove();
                    }
                }
            });
        });
    </script>
</x-app-layout>
