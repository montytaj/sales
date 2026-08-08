<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('contracts.index'), 'label' => __('projects.contracts')],
                ['label' => $contract->contract_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>العقد رقم: {{ $contract->contract_number }}
                </h2>
                <span class="badge bg-primary-subtle text-primary border border-primary fs-6">
                    {{ __('projects.contract_statuses.' . $contract->status) }}
                </span>
            </div>

            <div class="d-flex gap-2">
                @can('approve-contracts')
                    @if (!$contract->is_approved)
                        <form method="POST" action="{{ route('contracts.approve', $contract) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>اعتماد العقد رسمياً
                            </button>
                        </form>
                    @endif
                @endcan

                @can('create-projects')
                    @if ($contract->is_approved && !$contract->project)
                        <form method="POST" action="{{ route('contracts.convert-to-project', $contract) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-diagram-3 me-1"></i>تحويل إلى مشروع تنفيذي
                            </button>
                        </form>
                    @elseif ($contract->project)
                        <a href="{{ route('projects.show', $contract->project) }}" class="btn btn-outline-info">
                            <i class="bi bi-diagram-3 me-1"></i>الانتقال للمشروع المرتبط
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="row g-4 mb-4">
        <!-- Contract Summary -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-person-vcard me-2"></i>بيانات العميل والعقد</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">{{ __('customers.name') }}</span>
                        <h5 class="font-bold text-dark mb-0">{{ $contract->customer->name }}</h5>
                        <small class="text-muted">{{ $contract->customer->phone }}</small>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">نطاق العمل</span>
                        <p class="mb-0 text-dark font-semibold">{{ $contract->scope_of_work }}</p>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">تاريخ البدء</span>
                            <strong>{{ $contract->start_date->format('Y-m-d') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">تاريخ الانتهاء</span>
                            <strong>{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : 'غير محدد' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-currency-dollar me-2"></i>القيم المالية والمبالغ</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المبلغ الإجمالي:</span>
                        <span>{{ number_format($contract->total_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">الخصم:</span>
                        <span class="text-danger">- {{ number_format($contract->discount_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted">الضريبة ({{ setting('tax_percentage', 15.00) }}%):</span>
                        <span>+ {{ number_format($contract->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="fs-5 font-bold text-dark">صافي قيمة العقد:</span>
                        <span class="fs-4 font-bold text-success">{{ number_format($contract->net_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Milestones Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-calendar-check me-2"></i>جدول دفوعات ومراحل العقد</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">اسم الدفعة / المرحلة</th>
                            <th scope="col">تاريخ الاستحقاق</th>
                            <th scope="col">نوع القيمة</th>
                            <th scope="col">المبلغ المستحق</th>
                            <th scope="col">المبلغ المدفوع</th>
                            <th scope="col">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contract->paymentTerms as $index => $term)
                            @php
                                $isOverdue = $term->due_date < now()->startOfDay() && $term->paid_amount < $term->calculated_amount;
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger-subtle' : '' }}">
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td class="fw-semibold">{{ $term->milestone_name }}</td>
                                <td>{{ $term->due_date->format('Y-m-d') }}</td>
                                <td>{{ $term->amount_type === 'percentage' ? $term->value . '%' : 'مبلغ ثابت' }}</td>
                                <td class="fw-bold text-dark">{{ number_format($term->calculated_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td class="text-success">{{ number_format($term->paid_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td>
                                    @if ($isOverdue)
                                        <span class="badge bg-danger">متأخرة</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $term->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد دفوعات مجدولة لهذا العقد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
