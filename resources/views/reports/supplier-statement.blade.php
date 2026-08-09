<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.supplier_statement') ?? 'كشف حساب مورد']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.supplier_statement') ?? 'كشف حساب مورد تفصيلي'" :description="__('reports.supplier_statement_desc') ?? 'تقرير مالي تفصيلي بجميع فواتير التوريد والسداد والرصيد الجاري للمورد'">
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
    <x-report-filter-bar :action="route('reports.supplier-statement')" :branches="$branches">
        <div class="col-12 col-md-3">
            <label for="supplier_id" class="form-label font-semibold fs-7 text-slate-700">
                <i class="bi bi-truck me-1 text-primary"></i> {{ __('reports.select_supplier') ?? 'اختر المورد' }} <span class="text-danger">*</span>
            </label>
            <select name="supplier_id" id="supplier_id" class="form-select fs-7" required>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" {{ (string)$selectedSupplierId === (string)$s->id ? 'selected' : '' }}>
                        {{ $s->name }} ({{ $s->code }})
                    </option>
                @endforeach
            </select>
        </div>
    </x-report-filter-bar>

    @if ($statementData)
        <!-- KPI Summary Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-custom border-start border-4 border-slate-400 p-3">
                    <small class="text-slate-500 font-semibold">الرصيد الافتتاحي المستحق للمورد</small>
                    <div class="fs-4 font-bold text-slate-800 dir-ltr text-end text-md-start">
                        {{ number_format($statementData['opening_balance'], 2) }} <small class="fs-7">ر.س</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom border-start border-4 border-warning p-3">
                    <small class="text-slate-500 font-semibold">إجمالي المشتريات والتوريد (دائن)</small>
                    <div class="fs-4 font-bold text-warning dir-ltr text-end text-md-start">
                        {{ number_format($statementData['total_credit'], 2) }} <small class="fs-7">ر.س</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom border-start border-4 border-success p-3">
                    <small class="text-slate-500 font-semibold">إجمالي السداد والمدفوعات (مدين)</small>
                    <div class="fs-4 font-bold text-success dir-ltr text-end text-md-start">
                        {{ number_format($statementData['total_debit'], 2) }} <small class="fs-7">ر.س</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom border-start border-4 {{ $statementData['ending_balance'] > 0 ? 'border-danger' : 'border-emerald' }} p-3">
                    <small class="text-slate-500 font-semibold">الرصيد الختامي المستحق للمورد</small>
                    <div class="fs-3 font-extrabold {{ $statementData['ending_balance'] > 0 ? 'text-danger' : 'text-emerald' }} dir-ltr text-end text-md-start">
                        {{ number_format($statementData['ending_balance'], 2) }} <small class="fs-7">ر.س</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Table Card -->
        <div class="card card-custom overflow-hidden">
            <div class="card-header bg-slate-50 border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="font-bold text-slate-800 mb-0">
                    <i class="bi bi-list-columns-reverse me-1 text-primary"></i>
                    حركة حساب المورد: <span class="text-primary">{{ $statementData['supplier']->name }}</span> (الكود: {{ $statementData['supplier']->code }})
                </h6>
                <span class="badge bg-slate-200 text-slate-700 font-mono fs-7">
                    عدد الحركات: {{ count($statementData['ledger']) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="bg-slate-100 border-bottom border-slate-200">
                        <tr>
                            <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">التاريخ</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">رقم المستند</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">نوع المستند</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">البيان</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">مدين (سداد)</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">دائن (توريد)</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الرصيد الجاري</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7">الفرع</th>
                            <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">الملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 fs-7">
                        <tr class="bg-slate-50/50">
                            <td class="ps-3 font-medium text-slate-500">{{ request('from_date') ?? '-' }}</td>
                            <td class="font-mono text-slate-500">-</td>
                            <td><span class="badge bg-slate-200 text-slate-700">رصيد افتتاحي</span></td>
                            <td class="font-semibold text-slate-700">الرصيد المنقول للمورد قبل البداية</td>
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
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $row['document_type'] }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $row['document_type'] }}</span>
                                    @endif
                                </td>
                                <td class="text-slate-800 font-medium">{{ $row['description'] }}</td>
                                <td class="text-end font-mono text-success font-semibold">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                                </td>
                                <td class="text-end font-mono text-warning font-semibold">
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
                                    <x-empty-state icon="bi-truck-front" title="لا توجد حركات للمورد" description="لم يتم تسجيل توريدات أو مدفوعات لهذا المورد خلال الفترة المحددة." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100 border-top border-slate-300 font-bold fs-7">
                        <tr>
                            <td colspan="4" class="ps-3 text-slate-800">الإجمالي النهائي للتقرير:</td>
                            <td class="text-end text-success font-mono dir-ltr">{{ number_format($statementData['total_debit'], 2) }}</td>
                            <td class="text-end text-warning font-mono dir-ltr">{{ number_format($statementData['total_credit'], 2) }}</td>
                            <td class="text-end text-danger font-mono dir-ltr">{{ number_format($statementData['ending_balance'], 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
