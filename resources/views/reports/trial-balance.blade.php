<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'تقرير ميزان المراجعة']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'تقرير ميزان المراجعة' : 'Trial Balance Report' }}" description="استعراض الأرصدة الافتتاحية وحركات الفترة والأرصدة الختامية للجانبين المدين والدائن">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.trial-balance', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.trial-balance') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $from_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $to_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">مستوى الحسابات</label>
                    <select name="level" class="form-select form-select-sm rounded-3">
                        <option value="">كافة المستويات (1-5)</option>
                        <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>المستوى 1 (رئيسي)</option>
                        <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>المستوى 2</option>
                        <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>المستوى 3</option>
                        <option value="4" {{ request('level') == '4' ? 'selected' : '' }}>المستوى 4</option>
                        <option value="5" {{ request('level') == '5' ? 'selected' : '' }}>المستوى 5 (تشغيلي)</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trial Balance Table Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom border-slate-200 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-card-checklist me-2 text-primary"></i> أرصدة وحركات ميزان المراجعة</h5>
            <span class="badge {{ $is_balanced ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-3 py-1 fs-7">
                <i class="bi {{ $is_balanced ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                {{ $is_balanced ? 'الميزان متوازن' : 'الميزان غير متوازن' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-7">
                <thead class="table-dark text-center">
                    <tr>
                        <th rowspan="2" class="align-middle text-start ps-3" style="width: 12%;">رمز الحساب</th>
                        <th rowspan="2" class="align-middle text-start" style="width: 28%;">اسم الحساب</th>
                        <th colspan="2" class="border-bottom">الأرصدة الافتتاحية</th>
                        <th colspan="2" class="border-bottom">حركة الفترة</th>
                        <th colspan="2" class="border-bottom">الأرصدة الختامية</th>
                    </tr>
                    <tr>
                        <th style="width: 10%;">مدين</th>
                        <th style="width: 10%;">دائن</th>
                        <th style="width: 10%;">مدين</th>
                        <th style="width: 10%;">دائن</th>
                        <th style="width: 10%;">مدين</th>
                        <th style="width: 10%;">دائن</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td class="ps-3 font-mono font-semibold text-primary">{{ $r['code'] }}</td>
                            <td class="font-medium text-slate-800">{{ $r['name'] }} <small class="text-slate-400 ms-1">(م{{ $r['level'] }})</small></td>
                            <td class="text-end font-mono">{{ $r['opening_debit'] > 0 ? number_format($r['opening_debit'], 2) : '-' }}</td>
                            <td class="text-end font-mono">{{ $r['opening_credit'] > 0 ? number_format($r['opening_credit'], 2) : '-' }}</td>
                            <td class="text-end font-mono text-primary">{{ $r['period_debit'] > 0 ? number_format($r['period_debit'], 2) : '-' }}</td>
                            <td class="text-end font-mono text-danger">{{ $r['period_credit'] > 0 ? number_format($r['period_credit'], 2) : '-' }}</td>
                            <td class="text-end font-mono font-bold text-slate-900">{{ $r['ending_debit'] > 0 ? number_format($r['ending_debit'], 2) : '-' }}</td>
                            <td class="text-end font-mono font-bold text-slate-900">{{ $r['ending_credit'] > 0 ? number_format($r['ending_credit'], 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-slate-500">لا توجد حسابات مسجلة للفترة المحددة</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light font-bold fs-7">
                    <tr>
                        <td colspan="2" class="ps-3 text-start font-bold">الإجمالي العام (Total Summary):</td>
                        <td class="text-end font-mono">{{ number_format($totals['opening_debit'], 2) }}</td>
                        <td class="text-end font-mono">{{ number_format($totals['opening_credit'], 2) }}</td>
                        <td class="text-end font-mono text-primary">{{ number_format($totals['period_debit'], 2) }}</td>
                        <td class="text-end font-mono text-danger">{{ number_format($totals['period_credit'], 2) }}</td>
                        <td class="text-end font-mono text-success fs-6">{{ number_format($totals['ending_debit'], 2) }}</td>
                        <td class="text-end font-mono text-success fs-6">{{ number_format($totals['ending_credit'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
