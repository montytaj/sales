@props([
    'action' => '#',
    'branches' => null,
    'customers' => null,
    'suppliers' => null,
    'cashboxes' => null,
    'statuses' => null,
    'showDateRange' => true,
    'showBranch' => true,
])

@php
    $selectedBranch = request('branch_id');
    $selectedStatus = request('status');
    $fromDate = request('from_date', setting('system_start_date', date('Y-m-d')));
    $toDate = request('to_date', date('Y-m-d'));
    $preset = request('preset');

    $user = Auth::user();
    $canViewAllBranches = $user && $user->can('reports.view_all_branches');
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" x-data="{ advancedOpen: false }">
    <div class="card-body p-3.5 p-md-4">
        <form method="GET" action="{{ $action }}" id="reportFilterForm">
            <!-- Hidden Preset Selector Tracker -->
            <input type="hidden" name="preset" id="filterPresetInput" value="{{ request('preset') }}">

            <!-- Row 1: Time Range Presets, Dates & Branch (4 Equal 25% Columns) -->
            <div class="row g-3 mb-3 align-items-end">
                @if ($showDateRange)
                    <!-- 1. Quick Period Preset -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-calendar-range me-1 text-primary"></i> {{ __('reports.period_preset') ?? 'الفترة الزمنية السريعة' }}
                        </label>
                        <select class="form-select fs-7 py-2 rounded-3 border-slate-300" id="datePresetSelect" onchange="applyDatePreset(this.value)">
                            <option value="">-- {{ __('reports.custom_period') ?? 'فترة مخصصة' }} --</option>
                            <option value="today" {{ $preset === 'today' ? 'selected' : '' }}>{{ __('reports.preset_today') ?? 'اليوم' }}</option>
                            <option value="yesterday" {{ $preset === 'yesterday' ? 'selected' : '' }}>{{ __('reports.preset_yesterday') ?? 'أمس' }}</option>
                            <option value="this_week" {{ $preset === 'this_week' ? 'selected' : '' }}>{{ __('reports.preset_this_week') ?? 'هذا الأسبوع' }}</option>
                            <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>{{ __('reports.preset_this_month') ?? 'هذا الشهر' }}</option>
                            <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>{{ __('reports.preset_last_month') ?? 'الشهر السابق' }}</option>
                            <option value="this_quarter" {{ $preset === 'this_quarter' ? 'selected' : '' }}>{{ __('reports.preset_this_quarter') ?? 'هذا الربع' }}</option>
                            <option value="this_year" {{ $preset === 'this_year' ? 'selected' : '' }}>{{ __('reports.preset_this_year') ?? 'هذا العام' }}</option>
                        </select>
                    </div>

                    <!-- 2. From Date -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="from_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-calendar3 me-1 text-primary"></i> {{ __('reports.from_date') ?? 'من تاريخ' }}
                        </label>
                        <input type="date" name="from_date" id="from_date" class="form-control fs-7 py-2 rounded-3 border-slate-300" value="{{ $fromDate }}">
                    </div>

                    <!-- 3. To Date -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="to_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-calendar3 me-1 text-primary"></i> {{ __('reports.to_date') ?? 'إلى تاريخ' }}
                        </label>
                        <input type="date" name="to_date" id="to_date" class="form-control fs-7 py-2 rounded-3 border-slate-300" value="{{ $toDate }}">
                    </div>
                @endif

                @if ($showBranch && $branches && $canViewAllBranches)
                    <!-- 4. Branch -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="branch_id" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-building me-1 text-primary"></i> {{ __('reports.branch') ?? 'الفرع' }}
                        </label>
                        <select name="branch_id" id="branch_id" class="form-select fs-7 py-2 rounded-3 border-slate-300">
                            <option value="">-- {{ __('reports.all_branches') ?? 'جميع الفروع المصرحة' }} --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string)$selectedBranch === (string)$branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Row 2: Status, Extra Custom Slot (e.g. Customer) & Filter Submit Actions -->
            <div class="row g-3 align-items-end">
                @if ($statuses)
                    <!-- 1. Status Dropdown (col-md-4) -->
                    <div class="col-12 col-md-4">
                        <label for="status" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-info-circle me-1 text-primary"></i> {{ __('reports.status') ?? 'الحالة' }}
                        </label>
                        <select name="status" id="status" class="form-select fs-7 py-2 rounded-3 border-slate-300">
                            <option value="">-- {{ __('reports.all_statuses') ?? 'جميع الحالات' }} --</option>
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" {{ (string)$selectedStatus === (string)$key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- 2. Dynamic Extra Inputs Slot (Customer/Supplier) -->
                @if (isset($slot) && trim($slot))
                    {{ $slot }}
                @endif

                <!-- 3. Actions Submit & Reset Buttons -->
                <div class="col-12 col-md-auto ms-auto d-flex align-items-end gap-2 pt-2 pt-md-0">
                    <button type="submit" class="btn btn-primary font-bold shadow-sm fs-7 px-4 py-2 rounded-3 d-inline-flex align-items-center">
                        <i class="bi bi-funnel-fill me-1.5"></i> {{ __('reports.apply_filter') ?? 'تطبيق التصفية' }}
                    </button>
                    @if (request()->hasAny(['from_date', 'to_date', 'branch_id', 'status', 'customer_id', 'supplier_id', 'cashbox_id', 'search', 'preset']))
                        <a href="{{ strtok(url()->full(), '?') }}" class="btn btn-outline-secondary font-semibold fs-7 px-3 py-2 rounded-3 d-inline-flex align-items-center" title="{{ __('reports.clear_filters') ?? 'مسح الفلاتر' }}">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('reports.reset') ?? 'إعادة ضبط' }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Filter Chips Tag Bar -->
            @if (request()->hasAny(['from_date', 'to_date', 'branch_id', 'status', 'customer_id', 'supplier_id', 'preset']))
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top border-slate-200">
                    <small class="text-slate-500 font-semibold me-1"><i class="bi bi-funnel-fill text-primary me-1"></i>{{ __('reports.active_filters') ?? 'الفلاتر النشطة' }}:</small>
                    @if ($fromDate)
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200 fs-8 font-medium px-2.5 py-1">
                            {{ __('reports.from_date') }}: {{ $fromDate }}
                        </span>
                    @endif
                    @if ($toDate)
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200 fs-8 font-medium px-2.5 py-1">
                            {{ __('reports.to_date') }}: {{ $toDate }}
                        </span>
                    @endif
                    @if ($selectedBranch && $branches)
                        @php $bName = $branches->firstWhere('id', $selectedBranch)?->name; @endphp
                        @if ($bName)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8 font-medium px-2.5 py-1">
                                {{ __('reports.branch') }}: {{ $bName }}
                            </span>
                        @endif
                    @endif
                    @if ($selectedStatus && $statuses && isset($statuses[$selectedStatus]))
                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-8 font-medium px-2.5 py-1">
                            {{ __('reports.status') }}: {{ $statuses[$selectedStatus] }}
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    function applyDatePreset(presetValue) {
        document.getElementById('filterPresetInput').value = presetValue;
        const fromInput = document.getElementById('from_date');
        const toInput = document.getElementById('to_date');

        if (!presetValue) return;

        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        let from, to;

        switch(presetValue) {
            case 'today':
                from = to = formatDate(today);
                break;
            case 'yesterday':
                const y = new Date(today);
                y.setDate(y.getDate() - 1);
                from = to = formatDate(y);
                break;
            case 'this_week':
                const firstDayOfWeek = new Date(today);
                firstDayOfWeek.setDate(today.getDate() - today.getDay());
                from = formatDate(firstDayOfWeek);
                to = formatDate(today);
                break;
            case 'this_month':
                from = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                to = formatDate(today);
                break;
            case 'last_month':
                from = formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                to = formatDate(new Date(today.getFullYear(), today.getMonth(), 0));
                break;
            case 'this_quarter':
                const quarterMonth = Math.floor(today.getMonth() / 3) * 3;
                from = formatDate(new Date(today.getFullYear(), quarterMonth, 1));
                to = formatDate(today);
                break;
            case 'this_year':
                from = formatDate(new Date(today.getFullYear(), 0, 1));
                to = formatDate(today);
                break;
        }

        if (from && fromInput) fromInput.value = from;
        if (to && toInput) toInput.value = to;
    }
</script>
