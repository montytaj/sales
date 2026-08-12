<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-currency-exchange text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إدارة العملات وأسعار الصرف' : 'Currencies & Exchange Rates' }}
                </h2>
                <p class="text-muted fs-7 mb-0">{{ app()->getLocale() == 'ar' ? 'تعديل أسعار الصرف اليومية وإصدار المعاملات بالعملات الأجنبية' : 'Manage multi-currency exchange rates and transactions' }}</p>
            </div>
            <button type="button" class="btn btn-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                <i class="bi bi-plus-circle me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'إضافة عملة جديدة' : 'Add New Currency' }}
            </button>
        </div>
    </x-slot>

    <!-- Top KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'العملة الرئيسية للنظام' : 'System Base Currency'"
                :value="$baseCurrency->code ?? 'SAR'"
                :subtitle="$baseCurrency->name ?? 'ريال سعودي'"
                icon="bi-cash-coin"
                color="primary" />
        </div>

        <div class="col-12 col-md-4">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'إجمالي العملات النشطة' : 'Active Currencies'"
                :value="$currencies->where('is_active', true)->count()"
                :subtitle="app()->getLocale() == 'ar' ? 'عملات متاحة للمعاملات' : 'Active currencies'"
                icon="bi-globe2"
                color="emerald" />
        </div>

        <!-- Live Currency Exchange Calculator Widget -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-lift transition h-100" style="border-top: 4px solid #8b5cf6 !important;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-3 bg-purple-100 text-purple-600 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-calculator-fill fs-6"></i>
                    </div>
                    <h6 class="fw-bold text-slate-800 mb-0 fs-7">{{ app()->getLocale() == 'ar' ? 'حاسبة أسعار الصرف السريعة' : 'Quick Rate Calculator' }}</h6>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" id="calcAmount" class="form-control form-control-sm font-mono fw-bold" value="100" oninput="calculateLiveExchange()">
                    </div>
                    <div class="col-6">
                        <select id="calcCurrencySelect" class="form-select form-select-sm fs-7 fw-bold" onchange="calculateLiveExchange()">
                            @foreach($currencies as $c)
                                <option value="{{ $c->exchange_rate }}" data-code="{{ $c->code }}">{{ $c->code }} ({{ $c->symbol }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-2 text-center bg-light p-1.5 rounded-3">
                    <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'القيمة المعادلة:' : 'Equivalent:' }}</small>
                    <div id="calcResult" class="font-mono fw-black text-purple-600 fs-6">--</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currencies Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-slate-900 mb-0 fs-6">
                <i class="bi bi-table text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'قائمة العملات وأسعار الصرف المقابلة' : 'Currencies List' }}
            </h5>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-mono fs-8">
                {{ app()->getLocale() == 'ar' ? 'مقاسة بالنسبة لـ: ' : 'Base: ' }}{{ $baseCurrency->name ?? 'الريال السعودي' }} (1.00)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light fs-7">
                        <tr>
                            <th scope="col" class="ps-4">#</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'رمز العملة' : 'Code' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'اسم العملة' : 'Name' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'الرمز' : 'Symbol' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'سعر الصرف (مقابل العملة الرئيسية)' : 'Exchange Rate (vs Base)' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'نوع العملة' : 'Type' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th scope="col" class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        @foreach($currencies as $currency)
                            <tr>
                                <td class="ps-4 font-mono">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-slate-900 text-white font-mono fs-7 px-2.5 py-1 rounded-3">
                                        {{ $currency->code }}
                                    </span>
                                </td>
                                <td class="fw-bold text-slate-800">{{ $currency->name }}</td>
                                <td class="font-bold text-primary fs-6">{{ $currency->symbol ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 font-mono fw-extrabold text-slate-900 fs-6">
                                        <span>{{ number_format($currency->exchange_rate, 6) }}</span>
                                        <small class="fs-8 text-muted font-sans font-bold">
                                            ({{ $baseCurrency->code ?? 'SAR' }} / 1 {{ $currency->code }})
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($currency->is_base)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-star-fill me-1 text-warning"></i>{{ app()->getLocale() == 'ar' ? 'العملة الرئيسية' : 'Base Currency' }}
                                        </span>
                                    @else
                                        <span class="badge bg-slate-100 text-slate-600 border px-2.5 py-1 rounded-pill">
                                            {{ app()->getLocale() == 'ar' ? 'عملة أجنبية' : 'Foreign Currency' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($currency->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">{{ app()->getLocale() == 'ar' ? 'نشطة' : 'Active' }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">{{ app()->getLocale() == 'ar' ? 'معطلة' : 'Disabled' }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                        @if(!$currency->is_base)
                                            <form action="{{ route('currencies.setBase', $currency) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-action-icon btn-action-edit" title="تعيين كعملة رئيسية">
                                                    <i class="bi bi-star"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-action-icon btn-action-edit" data-bs-toggle="modal" data-bs-target="#editCurrencyModal{{ $currency->id }}" title="تعديل السعر">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if(!$currency->is_base)
                                            <form action="{{ route('currencies.destroy', $currency) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت تأكد من حذف هذه العملة؟' : 'Are you sure?' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action-icon btn-action-delete" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Currency Modal -->
                            <div class="modal fade" id="editCurrencyModal{{ $currency->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow-lg">
                                        <div class="modal-header bg-light border-bottom p-3">
                                            <h5 class="modal-title font-bold fs-6 text-slate-900">
                                                <i class="bi bi-pencil-square me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'تعديل سعر صرف ' : 'Edit ' }}{{ $currency->name }} ({{ $currency->code }})
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('currencies.update', $currency) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'اسم العملة' : 'Currency Name' }}</label>
                                                    <input type="text" name="name" class="form-control fs-7" value="{{ $currency->name }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'رمز العملة (Symbol)' : 'Symbol' }}</label>
                                                    <input type="text" name="symbol" class="form-control fs-7 font-mono" value="{{ $currency->symbol }}">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-bold fs-7 text-slate-700">
                                                        {{ app()->getLocale() == 'ar' ? 'سعر الصرف مقابل العملة الرئيسية (' . ($baseCurrency->code ?? 'SAR') . ')' : 'Exchange Rate vs Base' }}
                                                    </label>
                                                    <input type="number" step="0.000001" name="exchange_rate" class="form-control fs-7 font-mono fw-bold" value="{{ $currency->exchange_rate }}" {{ $currency->is_base ? 'readonly' : '' }} required>
                                                    <small class="text-muted fs-8 d-block mt-1">1 {{ $currency->code }} = [سعر الصرف] {{ $baseCurrency->code ?? 'SAR' }}</small>
                                                </div>

                                                @if(!$currency->is_base)
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck{{ $currency->id }}" value="1" {{ $currency->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-input-label font-semibold fs-7" for="activeCheck{{ $currency->id }}">
                                                            {{ app()->getLocale() == 'ar' ? 'تفعيل العملة للمعاملات الفورية' : 'Active for transactions' }}
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light p-3">
                                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-bold">{{ app()->getLocale() == 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Add New Currency -->
    <div class="modal fade" id="addCurrencyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom p-3">
                    <h5 class="modal-title font-bold fs-6 text-slate-900">
                        <i class="bi bi-plus-circle me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'إضافة عملة وسعر صرف جديد' : 'Add New Currency' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('currencies.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'كود العملة (ISO Code)' : 'Currency Code (e.g. GBP)' }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control fs-7 font-mono text-uppercase" placeholder="GBP" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'اسم العملة بالكامل' : 'Currency Name' }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control fs-7" placeholder="جنيه إسترليني" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'الرمز (Symbol)' : 'Symbol' }}</label>
                            <input type="text" name="symbol" class="form-control fs-7 font-mono" placeholder="£">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold fs-7 text-slate-700">{{ app()->getLocale() == 'ar' ? 'سعر الصرف التبادلي' : 'Exchange Rate' }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.000001" name="exchange_rate" class="form-control fs-7 font-mono fw-bold" value="1.000000" required>
                            <small class="text-muted fs-8 d-block mt-1">مقدار ما تساويه الوحدة الواحدة بالعملة الرئيسية ({{ $baseCurrency->code ?? 'SAR' }})</small>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeCheckNew" value="1" checked>
                            <label class="form-check-input-label font-semibold fs-7" for="activeCheckNew">
                                {{ app()->getLocale() == 'ar' ? 'تفعيل العملة فوراً بالنظام' : 'Active immediately' }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-bold">{{ app()->getLocale() == 'ar' ? 'إضافة العملة' : 'Add Currency' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function calculateLiveExchange() {
            const amount = parseFloat(document.getElementById('calcAmount').value) || 0;
            const select = document.getElementById('calcCurrencySelect');
            const rate = parseFloat(select.value) || 1;
            const selectedOpt = select.options[select.selectedIndex];
            const code = selectedOpt.getAttribute('data-code') || '';
            const baseCode = '{{ $baseCurrency->code ?? "SAR" }}';

            const result = amount * rate;
            document.getElementById('calcResult').innerText = result.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + baseCode;
        }

        document.addEventListener('DOMContentLoaded', function() {
            calculateLiveExchange();
        });
    </script>
</x-app-layout>
