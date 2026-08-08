@props([
    'action' => url()->current(),
    'searchPlaceholder' => __('general.search_placeholder') ?? 'ابحث بالاسم، الرقم، التفاصيل...',
    'statuses' => [],
    'branches' => [],
    'showDateFilter' => false
])

@php
    $hasActiveFilters = request()->filled('search') || request()->filled('status') || request()->filled('branch_id') || request()->filled('date_from') || request()->filled('date_to');
@endphp

<div class="card card-custom p-3 mb-4 shadow-sm">
    <form method="GET" action="{{ $action }}" id="filterForm">
        <div class="row g-2 align-items-center">
            <!-- Search Field -->
            <div class="col-12 col-md">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-sm border-start-0 ps-0" 
                           placeholder="{{ $searchPlaceholder }}" 
                           value="{{ request('search') }}">
                </div>
            </div>

            <!-- Status Filter -->
            @if(!empty($statuses))
                <div class="col-6 col-md-auto" style="min-width: 140px;">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('general.all_statuses') ?? 'جميع الحالات' }}</option>
                        @foreach($statuses as $value => $title)
                            <option value="{{ $value }}" {{ request('status') == (string)$value ? 'selected' : '' }}>
                                {{ $title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Branch Filter -->
            @if(!empty($branches))
                <div class="col-6 col-md-auto" style="min-width: 140px;">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('general.all_branches') ?? 'جميع الفروع' }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Date Range Filter -->
            @if($showDateFilter)
                <div class="col-6 col-md-auto">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" onchange="this.form.submit()" placeholder="من تاريخ">
                </div>
                <div class="col-6 col-md-auto">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" onchange="this.form.submit()" placeholder="إلى تاريخ">
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="col-12 col-md-auto d-flex gap-1 ms-auto">
                <button type="submit" class="btn btn-sm btn-primary-custom px-3">
                    <i class="bi bi-funnel"></i>
                    <span>{{ __('general.filter') ?? 'فلترة' }}</span>
                </button>

                @if($hasActiveFilters)
                    <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary px-2.5" title="{{ __('general.clear_filters') ?? 'مسح الفلاتر' }}">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Active Filter Chips -->
        @if($hasActiveFilters)
            <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top flex-wrap fs-7">
                <span class="text-muted font-medium">{{ __('general.active_filters') ?? 'الفلاتر النشطة:' }}</span>
                
                @if(request('search'))
                    <span class="badge bg-light text-dark border px-2 py-1">
                        بحث: {{ request('search') }}
                    </span>
                @endif

                @if(request('status'))
                    <span class="badge bg-light text-dark border px-2 py-1">
                        الحالة: {{ $statuses[request('status')] ?? request('status') }}
                    </span>
                @endif

                @if(request('branch_id'))
                    <span class="badge bg-light text-dark border px-2 py-1">
                        الفرع المحدد
                    </span>
                @endif

                <a href="{{ $action }}" class="text-danger font-semibold text-decoration-none ms-auto fs-8">
                    {{ __('general.reset_all') ?? 'إعادة ضبط الكل' }}
                </a>
            </div>
        @endif
    </form>
</div>
