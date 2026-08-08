<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.customer_statement') ?? 'كشف حساب عميل']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.customer_statement') ?? 'كشف حساب عميل تفصيلي'" :description="__('reports.customer_statement_desc') ?? 'تقرير مالي تفصيلي بجميع الحركات المدينة والدائنة والرصيد الجاري للعميل'">
        <x-slot name="actions">
            @if ($statementData)
                <div class="d-flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-emerald-custom font-semibold shadow-sm fs-7">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> تصدير Excel
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank" class="btn btn-secondary-custom font-semibold shadow-sm fs-7">
                        <i class="bi bi-printer-fill me-1"></i> طباعة / PDF
                    </a>
                </div>
            @endif
        </x-slot>
    </x-page-header>

    <!-- Filter Bar -->
    <x-report-filter-bar :action="route('reports.customer-statement')" :branches="$branches">
        <div class="col-12 col-sm-6 col-lg-2.5">
            <label for="customer_id" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                <i class="bi bi-person me-1 text-primary"></i> {{ __('reports.select_customer') ?? 'اختر العميل' }} <span class="text-danger">*</span>
            </label>
            <select name="customer_id" id="customer_id" class="form-select fs-7" required>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" {{ (string)$selectedCustomerId === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->code }})
                    </option>
                @endforeach
            </select>
        </div>
    </x-report-filter-bar>

    @if ($statementData)
        <!-- KPI Summary Cards Grid (Centered Numbers & Standardized Design) -->
        <div class="row g-3.5 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="الرصيد الافتتاحي"
                    :value="number_format($statementData['opening_balance'], 2)"
                    :currency="setting('currency', 'SAR')"
                    subtitle="الرصيد المنقول قبل البداية"
                    icon="bi-wallet2"
                    color="slate" />
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="إجمالي الحركات المدينة (فواتير)"
                    :value="number_format($statementData['total_debit'], 2)"
                    :currency="setting('currency', 'SAR')"
                    subtitle="الفواتير المستحقة"
                    icon="bi-arrow-up-right-circle-fill"
                    color="danger" />
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="إجمالي الحركات الدائنة (سداد)"
                    :value="number_format($statementData['total_credit'], 2)"
                    :currency="setting('currency', 'SAR')"
                    subtitle="سندات ومبالغ المقبوضات"
                    icon="bi-arrow-down-left-circle-fill"
                    color="success" />
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="الرصيد الختامي المستحق"
                    :value="number_format($statementData['ending_balance'], 2)"
                    :currency="setting('currency', 'SAR')"
                    subtitle="صافي المستحق النهائي"
                    icon="bi-calculator-fill"
                    :color="$statementData['ending_balance'] > 0 ? 'primary' : 'emerald'" />
            </div>
        </div>


        <!-- Ledger Table Card -->
        <div class="card card-custom overflow-hidden">
            <div class="card-header bg-slate-50 border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="font-bold text-slate-800 mb-0">
                    <i class="bi bi-list-columns-reverse me-1 text-primary"></i>
                    حركة حساب العميل: <span class="text-primary">{{ $statementData['customer']->name }}</span> (الكود: {{ $statementData['customer']->code }})
                </h6>
                <span class="badge bg-slate-200 text-slate-700 font-mono fs-7">
                    عدد الحركات: {{ count($statementData['ledger']) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-slate-100 border-bottom border-slate-200">
                        <tr>
                            <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">التاريخ</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">رقم المستند</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">نوع المستند</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">البيان</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">مدين (فواتير)</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">دائن (سداد)</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الرصيد الجاري</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">الفرع</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">الملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 fs-7">
                        <!-- Opening Balance Row -->
                        <tr class="bg-slate-50/50">
                            <td class="ps-3 font-medium text-slate-500">{{ request('from_date') ?? '-' }}</td>
                            <td class="font-mono text-slate-500">-</td>
                            <td><span class="badge bg-slate-200 text-slate-700">رصيد افتتاحي</span></td>
                            <td class="font-semibold text-slate-700">الرصيد المنقول قبل بداية الفترة المحددة</td>
                            <td class="text-end text-slate-400 font-mono">-</td>
                            <td class="text-end text-slate-400 font-mono">-</td>
                            <td class="text-end font-bold text-slate-800 font-mono dir-ltr">{{ number_format($statementData['opening_balance'], 2) }}</td>
                            <td class="text-slate-500">-</td>
                            <td class="pe-3 text-slate-500">-</td>
                        </tr>

                        @forelse ($statementData['ledger'] as $row)
                            <tr>
                                <td class="ps-3 font-mono text-slate-600 dir-ltr text-end text-md-start">{{ $row['date'] }}</td>
                                <td class="font-mono font-bold text-slate-900">
                                    <a href="{{ $row['link'] }}" class="text-decoration-none hover-primary">{{ $row['document_number'] }}</a>
                                </td>
                                <td>
                                    @if ($row['debit'] > 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $row['document_type'] }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $row['document_type'] }}</span>
                                    @endif
                                </td>
                                <td class="text-slate-800 font-medium">{{ $row['description'] }}</td>
                                <td class="text-end font-mono text-danger font-semibold">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                                </td>
                                <td class="text-end font-mono text-success font-semibold">
                                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                                </td>
                                <td class="text-end font-mono font-bold text-slate-900 dir-ltr">
                                    {{ number_format($row['running_balance'], 2) }}
                                </td>
                                <td class="text-slate-600">{{ $row['branch'] ?? '-' }}</td>
                                <td class="pe-3 text-slate-500 fs-8">{{ $row['notes'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <x-empty-state icon="bi-receipt-cutoff" title="لا توجد حركات في هذه الفترة" description="لم يتم تسجيل فواتير أو سندات قبض للعميل خلال الفترة الزمنيّة المحددة." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- Summary Footer -->
                    <tfoot class="bg-slate-100 border-top border-slate-300 font-bold fs-7">
                        <tr>
                            <td colspan="4" class="ps-3 text-slate-800">الإجمالي النهائي للتقرير:</td>
                            <td class="text-end text-danger font-mono dir-ltr">{{ number_format($statementData['total_debit'], 2) }}</td>
                            <td class="text-end text-success font-mono dir-ltr">{{ number_format($statementData['total_credit'], 2) }}</td>
                            <td class="text-end text-primary font-mono dir-ltr">{{ number_format($statementData['ending_balance'], 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
