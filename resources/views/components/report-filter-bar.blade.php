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
    $fromDate = request('from_date');
    $toDate = request('to_date');
    $preset = request('preset');

    // Preset Date Calculations for quick JS/preset selection
    $user = Auth::user();
    $canViewAllBranches = $user && $user->can('reports.view_all_branches');
@endphp

<div class="card card-custom mb-4 border-0 shadow-sm" x-data="{ advancedOpen: false }">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ $action }}" id="reportFilterForm">
            <!-- Hidden Preset Selector Tracker -->
            <input type="hidden" name="preset" id="filterPresetInput" value="{{ request('preset') }}">

            <!-- Basic Filter Row -->
            <div class="row g-2.5 align-items-end">
                @if ($showDateRange)
                    <!-- Date Presets Dropdown -->
                    <div class="col-12 col-sm-6 col-lg-2.5">
                        <label class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-calendar-range me-1 text-primary"></i> {{ __('reports.period_preset') ?? 'الفترة الزمنية السريعة' }}
                        </label>
                        <select class="form-select fs-7" id="datePresetSelect" onchange="applyDatePreset(this.value)">
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

                    <div class="col-6 col-lg-2">
                        <label for="from_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">{{ __('reports.from_date') ?? 'من تاريخ' }}</label>
                        <input type="date" name="from_date" id="from_date" class="form-control fs-7" value="{{ $fromDate }}">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="to_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">{{ __('reports.to_date') ?? 'إلى تاريخ' }}</label>
                        <input type="date" name="to_date" id="to_date" class="form-control fs-7" value="{{ $toDate }}">
                    </div>
                @endif

                @if ($showBranch && $branches && $canViewAllBranches)
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="branch_id" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                            <i class="bi bi-building me-1 text-primary"></i> {{ __('reports.branch') ?? 'الفرع' }}
                        </label>
                        <select name="branch_id" id="branch_id" class="form-select fs-7">
                            <option value="">-- {{ __('reports.all_branches') ?? 'جميع الفروع المصرحة' }} --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string)$selectedBranch === (string)$branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($statuses)
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="status" class="form-label font-semibold fs-7 text-slate-700 mb-1">{{ __('reports.status') ?? 'الحالة' }}</label>
                        <select name="status" id="status" class="form-select fs-7">
                            <option value="">-- {{ __('reports.all_statuses') ?? 'جميع الحالات' }} --</option>
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" {{ (string)$selectedStatus === (string)$key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Dynamic Extra Inputs Slot -->
                @if (isset($slot) && trim($slot))
                    {{ $slot }}
                @endif

                <!-- Actions -->
                <div class="col-12 col-md-auto ms-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom shadow-sm font-semibold fs-7 px-3">
                        <i class="bi bi-filter me-1"></i> {{ __('reports.apply_filter') ?? 'تطبيق التقرير' }}
                    </button>
                    @if (request()->hasAny(['from_date', 'to_date', 'branch_id', 'status', 'customer_id', 'supplier_id', 'cashbox_id', 'search', 'preset']))
                        <a href="{{ strtok(url()->full(), '?') }}" class="btn btn-secondary-custom font-semibold fs-7 px-3" title="{{ __('reports.clear_filters') ?? 'مسح الفلاتر' }}">
                            <i class="bi bi-x-lg me-1"></i> {{ __('reports.reset') ?? 'إعادة ضبط' }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Filter Chips -->
            @if (request()->hasAny(['from_date', 'to_date', 'branch_id', 'status', 'customer_id', 'supplier_id', 'preset']))
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top border-slate-100">
                    <small class="text-slate-500 font-semibold me-1"><i class="bi bi-funnel-fill text-primary me-1"></i>{{ __('reports.active_filters') ?? 'الفلاتر النشطة' }}:</small>
                    @if ($fromDate)
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200 fs-8 font-medium">
                            {{ __('reports.from_date') }}: {{ $fromDate }}
                        </span>
                    @endif
                    @if ($toDate)
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200 fs-8 font-medium">
                            {{ __('reports.to_date') }}: {{ $toDate }}
                        </span>
                    @endif
                    @if ($selectedBranch && $branches)
                        @php $bName = $branches->firstWhere('id', $selectedBranch)?->name; @endphp
                        @if ($bName)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8 font-medium">
                                {{ __('reports.branch') }}: {{ $bName }}
                            </span>
                        @endif
                    @endif
                    @if ($selectedStatus && $statuses && isset($statuses[$selectedStatus]))
                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-8 font-medium">
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
