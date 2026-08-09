<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.financial') ?? 'حركة الخزن والمالية']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.financial_report') ?? 'تقرير حركة الخزن والسندات النقدية'" :description="__('reports.financial_desc') ?? 'تحليل المقبوضات والمصروفات والتدفق النقدي الصافي على مستوى الخزن والفروع'">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-emerald-custom font-semibold shadow-sm fs-7">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> تصدير Excel
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filter Bar -->
    <x-report-filter-bar 
        :action="route('reports.financial')" 
        :branches="$branches">
        <div class="col-12 col-md-3">
            <label for="cashbox_id" class="form-label font-semibold fs-7 text-slate-700">الخزنة</label>
            <select name="cashbox_id" id="cashbox_id" class="form-select fs-7">
                <option value="">-- جميع الخزن --</option>
                @foreach ($cashboxes as $cb)
                    <option value="{{ $cb->id }}" {{ (string)request('cashbox_id') === (string)$cb->id ? 'selected' : '' }}>
                        {{ $cb->name }} ({{ $cb->branch?->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label for="type" class="form-label font-semibold fs-7 text-slate-700">نوع الحركة</label>
            <select name="type" id="type" class="form-select fs-7">
                <option value="">-- المقبوضات والمصروفات --</option>
                <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>سندات قبض (وارد)</option>
                <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>سندات صرف (صادر)</option>
            </select>
        </div>
    </x-report-filter-bar>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <x-kpi-card title="إجمالي المقبوضات (وارد)" :value="number_format($total_receipts, 2) . ' ر.س'" icon="bi-arrow-down-left-circle" color="emerald" subtitle="سندات القبض المعتمدة" />
        </div>
        <div class="col-6 col-md-4">
            <x-kpi-card title="إجمالي المدفوعات (صادر)" :value="number_format($total_payments, 2) . ' ر.س'" icon="bi-arrow-up-right-circle" color="danger" subtitle="سندات الصرف المعتمدة" />
        </div>
        <div class="col-12 col-md-4">
            <x-kpi-card title="الصافي النقدي بالفترة" :value="number_format($net_cashflow, 2) . ' ر.س'" icon="bi-bank" color="primary" subtitle="صافي التدفق النقدي" />
        </div>
    </div>

    <!-- Vouchers Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">رقم السند</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">النوع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الجهة / العميل / المورد</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الخزنة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">المبلغ</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">طريقة الدفع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الحالة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($vouchers as $vch)
                        <tr>
                            <td class="ps-3 font-mono font-bold text-slate-900">
                                <a href="{{ route('payments.show', $vch) }}" class="text-decoration-none hover-primary">{{ $vch->voucher_number }}</a>
                            </td>
                            <td>
                                @if ($vch->type === 'receipt')
                                    <span class="badge bg-emerald-subtle text-emerald border border-emerald-subtle"><i class="bi bi-arrow-down-left me-1"></i>سند قبض</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-arrow-up-right me-1"></i>سند صرف</span>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-800">
                                {{ $vch->customer?->name ?? $vch->supplier?->name ?? '-' }}
                            </td>
                            <td class="text-slate-700">{{ $vch->cashbox?->name }}</td>
                            <td class="text-end font-mono font-bold {{ $vch->type === 'receipt' ? 'text-emerald' : 'text-danger' }} dir-ltr">
                                {{ number_format($vch->amount, 2) }}
                            </td>
                            <td class="text-slate-600">{{ __('payments.methods.' . $vch->payment_method) ?? $vch->payment_method }}</td>
                            <td>
                                <x-status-badge :status="$vch->status" />
                            </td>
                            <td class="pe-3 font-mono text-slate-500 dir-ltr text-end text-md-start">{{ $vch->payment_date ? $vch->payment_date->format('Y-m-d') : $vch->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-0">
                                <x-empty-state icon="bi-wallet2" title="لا توجد سندات نقدية" description="لم يتم العثور على حركات خزن أو سندات تطابق الفلترة المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vouchers->hasPages())
            <div class="card-footer bg-white border-top border-slate-100 py-3">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
