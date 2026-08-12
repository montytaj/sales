<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'تقرير دفتر الأستاذ العام']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'تقرير دفتر الأستاذ العام' : 'General Ledger Report' }}" description="كشف حساب تفصيلي وشامل لأي حساب من شجرة الحسابات يوضح قيود المدين والدائن والرصيد الجاري">
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.general-ledger') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">اختيار الحساب (من شجرة الحسابات)</label>
                    <select name="account_id" class="form-select form-select-sm rounded-3 select2">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ (string)request('account_id', $selected_account?->id) === (string)$acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }} ({{ $acc->nature === 'debit' ? 'مدين' : 'دائن' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $from_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $to_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Details Summary Banner -->
    @if($selected_account)
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card card-custom border-0 shadow-sm p-3 bg-white border-start border-4 border-primary">
                    <span class="fs-8 text-slate-500 font-bold uppercase">اسم الحساب المحدد</span>
                    <h5 class="font-bold text-slate-900 mb-0 mt-1">{{ $selected_account->code }} - {{ $selected_account->name }}</h5>
                </div>
            </div>
            <div class="col-12 col-md-4">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-info">
                <span class="fs-8 text-slate-500 uppercase font-bold">الرصيد الافتتاحي</span>
                <h5 class="font-bold text-info mb-0 mt-1">{{ number_format($opening_balance, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h5>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-success">
                <span class="fs-8 text-slate-500 uppercase font-bold">الرصيد الختامي الجاري</span>
                <h5 class="font-bold text-success mb-0 mt-1">{{ number_format($ending_balance, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h5>
            </div>
        </div>
        </div>

        <!-- Ledger Transactions Table Card -->
        <div class="card card-custom border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-bottom border-slate-200">
                <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-journal-bookmark me-2 text-primary"></i> حركة الحساب التفصيلية</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">التاريخ</th>
                            <th>رقم المستند / القيد</th>
                            <th>المرجع / النوع</th>
                            <th>البيان والوصف</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end pe-4">الرصيد الجاري</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                            <tr>
                                <td class="ps-4 text-slate-700 font-medium">{{ $e['date'] }}</td>
                                <td class="font-mono text-primary font-semibold">{{ $e['doc_no'] }}</td>
                                <td><span class="badge bg-slate-100 text-slate-700 border px-2 py-1 fs-8">{{ $e['type'] }}</span></td>
                                <td class="text-slate-800">{{ $e['description'] }}</td>
                                <td class="text-end font-mono text-primary font-semibold">{{ $e['debit'] > 0 ? number_format($e['debit'], 2) : '-' }}</td>
                                <td class="text-end font-mono text-danger font-semibold">{{ $e['credit'] > 0 ? number_format($e['credit'], 2) : '-' }}</td>
                                <td class="text-end pe-4 font-mono font-bold text-slate-900">{{ number_format($e['running_balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-slate-500">لا توجد حركات مسجلة لهذا الحساب خلال الفترة المحددة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
