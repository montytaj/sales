<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'كشف أرصدة الحسابات']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'تقرير أرصدة وميزان شجرة الحسابات' : 'Account Balances Report' }}" description="استعراض الهيكل الشجري للحسابات الرئيسية والفرعية مع الأرصدة الحالية وطبيعة كل حساب">
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.account-balances') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-10">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">تاريخ الأرصدة (كما في تاريخ)</label>
                    <input type="date" name="as_of_date" value="{{ $as_of_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter me-1"></i> عرض
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tree Balances Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom border-slate-200">
            <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-diagram-3 me-2 text-primary"></i> دليل أرصدة شجرة الحسابات</h5>
        </div>
        <div class="card-body p-4">
            <div class="list-group list-group-flush">
                @foreach($account_tree as $root)
                    <div class="border rounded-3 p-3 mb-3 bg-slate-50">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-bold text-slate-900 mb-0 fs-6">
                                <span class="badge bg-primary me-2 font-mono">{{ $root->code }}</span>
                                {{ $root->name }}
                            </h6>
                            <span class="badge bg-white text-slate-800 border font-mono fs-7 font-bold px-3 py-1">
                                الرصيد: {{ number_format($root->balance, 2) }} {{ currency() }}
                            </span>
                        </div>

                        @if($root->children && $root->children->count() > 0)
                            <div class="card-body p-0 border-top">
                                <div class="list-group list-group-flush fs-7">
                                    @foreach($root->children as $child)
                                        <div class="list-group-item d-flex justify-content-between align-items-center ps-4 pe-4 py-2 hover-bg-slate-50">
                                            <span>
                                                <i class="bi bi-arrow-return-left text-slate-400 me-2"></i>
                                                [{{ $child->code }}] {{ $child->name }}
                                            </span>
                                            <span class="font-mono font-semibold text-slate-900">{{ number_format($child->balance, 2) }} {{ currency() }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
