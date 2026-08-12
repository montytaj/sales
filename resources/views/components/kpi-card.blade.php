@props([
    'title',
    'value',
    'currency' => null,
    'subtitle' => null,
    'icon' => 'bi-graph-up-arrow',
    'color' => 'primary',
    'trend' => null,
    'trendUp' => true,
    'url' => null,
    'date' => null,
    'actionText' => null,
    'badgeText' => null,
    'infoTooltip' => null,
    'footerText' => null,
])

@php
    $displayDate = $date ?? \Carbon\Carbon::now()->format('Y-m-d');
    $isRtl = app()->getLocale() == 'ar';
    
    // Check if numeric value is zero or string empty/zero
    $numericVal = is_numeric(str_replace(',', '', (string)$value)) ? (float)str_replace(',', '', (string)$value) : null;
    $hasNoData = ($value === null || $value === '' || $value === '—' || $value === '-' || ($numericVal !== null && $numericVal == 0));

    $colors = [
        'primary'   => ['accent' => '#2563eb', 'bg' => 'rgba(37, 99, 235, 0.06)',  'icon_bg' => 'rgba(37, 99, 235, 0.12)',  'text' => '#2563eb'],
        'success'   => ['accent' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.06)', 'icon_bg' => 'rgba(16, 185, 129, 0.12)', 'text' => '#059669'],
        'emerald'   => ['accent' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.06)', 'icon_bg' => 'rgba(16, 185, 129, 0.12)', 'text' => '#059669'],
        'danger'    => ['accent' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.06)',  'icon_bg' => 'rgba(239, 68, 68, 0.12)',  'text' => '#dc2626'],
        'warning'   => ['accent' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.06)', 'icon_bg' => 'rgba(245, 158, 11, 0.12)', 'text' => '#d97706'],
        'amber'     => ['accent' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.06)', 'icon_bg' => 'rgba(245, 158, 11, 0.12)', 'text' => '#d97706'],
        'info'      => ['accent' => '#0284c7', 'bg' => 'rgba(2, 132, 199, 0.06)',  'icon_bg' => 'rgba(2, 132, 199, 0.12)',  'text' => '#0284c7'],
        'purple'    => ['accent' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.06)', 'icon_bg' => 'rgba(139, 92, 246, 0.12)', 'text' => '#7c3aed'],
        'cyan'      => ['accent' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.06)',  'icon_bg' => 'rgba(6, 182, 212, 0.12)',  'text' => '#0891b2'],
        'indigo'    => ['accent' => '#6366f1', 'bg' => 'rgba(99, 102, 241, 0.06)', 'icon_bg' => 'rgba(99, 102, 241, 0.12)', 'text' => '#4f46e5'],
        'slate'     => ['accent' => '#64748b', 'bg' => 'rgba(100, 116, 139, 0.06)','icon_bg' => 'rgba(100, 116, 139, 0.12)','text' => '#475569'],
        'secondary' => ['accent' => '#64748b', 'bg' => 'rgba(100, 116, 139, 0.06)','icon_bg' => 'rgba(100, 116, 139, 0.12)','text' => '#475569'],
    ];

    $c = $colors[$color] ?? $colors['primary'];
    $circlePosition = $isRtl ? 'left: -15px;' : 'right: -15px;';
@endphp

<div class="card border rounded-4 bg-white hover-lift transition-all h-100 position-relative overflow-hidden p-3.5 d-flex flex-column justify-content-between shadow-2xs" 
     style="border: 1.5px solid rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.16) !important; border-radius: 1.25rem !important; min-height: 180px;">

    <!-- Background Semi-Circular Soft Glow Shape -->
    <div style="position: absolute; top: -15px; {{ $circlePosition }} width: 85px; height: 85px; border-radius: 50%; background: {{ $c['bg'] }}; pointer-events: none; opacity: 0.85; z-index: 0;"></div>

    <!-- Header Row: Left Info Button & Right Status Badge + Icon -->
    <div class="d-flex align-items-center justify-content-between w-100 position-relative z-1 mb-2">
        <!-- Info Icon Circle Button -->
        <div class="rounded-circle border d-flex align-items-center justify-content-center text-slate-500 bg-white shadow-2xs" 
             style="width: 28px; height: 28px; font-size: 0.85rem; border-color: rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.16) !important; cursor: pointer;"
             data-bs-toggle="tooltip" 
             title="{{ $infoTooltip ?? ($subtitle ?? $title) }}">
            <i class="bi bi-info-circle"></i>
        </div>

        <!-- Status Tag & Icon Circle -->
        <div class="d-flex align-items-center gap-1.5">
            @if($badgeText)
                <span class="badge rounded-pill bg-slate-100 text-slate-700 border border-slate-200 fs-8 font-medium px-2 py-0.5 d-inline-flex align-items-center gap-1">
                    <span>{{ $badgeText }}</span>
                </span>
            @elseif($hasNoData)
                <span class="badge rounded-pill bg-rose-50 text-rose-600 border border-rose-100 fs-8 font-medium px-2 py-0.5 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-database-exclamation text-rose-500 fs-8"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'لا توجد بيانات' : 'No Data' }}</span>
                </span>
            @else
                <span class="badge rounded-pill bg-emerald-50 text-emerald-700 border border-emerald-100 fs-8 font-medium px-2 py-0.5 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-check-circle-fill text-emerald-500 fs-8"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'مباشر' : 'Live' }}</span>
                </span>
            @endif

            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-2xs" 
                 style="width: 36px; height: 36px; background: {{ $c['icon_bg'] }}; color: {{ $c['text'] }}; flex-shrink: 0;">
                <i class="bi {{ $icon }} fs-6"></i>
            </div>
        </div>
    </div>

    <!-- Center Horizontal Indicator Line -->
    <div class="mx-auto my-1.5 position-relative z-1" style="width: 36px; height: 3.5px; background: #0f172a; border-radius: 4px;"></div>

    <!-- Center Content: Value, Title & Subtitle -->
    <div class="text-center my-auto py-1 position-relative z-1">
        <div class="fw-black text-slate-900 font-mono text-center d-flex align-items-center justify-content-center gap-1 flex-wrap mb-1" style="font-size: 1.55rem; line-height: 1.2;">
            @if($hasNoData && !is_numeric($value))
                <span>{{ $value }}</span>
            @else
                <span>{{ $value }}</span>
                @if($currency)
                    <span class="fs-8 font-sans font-bold text-slate-500 ms-0.5">
                        {{ $currency }}
                    </span>
                @endif
            @endif
        </div>
        <h6 class="fs-7 font-bold text-slate-700 mb-0.5 text-center" style="letter-spacing: -0.2px;">
            {{ $title }}
        </h6>
        @if($subtitle)
            <div class="fs-8 text-slate-500 text-center font-medium">{{ $subtitle }}</div>
        @endif
    </div>

    <!-- Footer Row: Action Link or Custom Footer Status -->
    <div class="text-center pt-1.5 border-top border-slate-100 mt-1 position-relative z-1">
        @if($url)
            <a href="{{ $url }}" class="text-decoration-none text-slate-800 hover-primary font-bold fs-7 d-inline-flex align-items-center justify-content-center gap-1">
                <span>{{ $actionText ?? (app()->getLocale() == 'ar' ? 'عرض التفاصيل' : 'View Details') }}</span>
                <i class="bi bi-arrow-left text-primary fs-7"></i>
            </a>
        @elseif($footerText)
            <div class="fs-8 text-slate-500 font-medium text-center d-flex align-items-center justify-content-center gap-1">
                <span>{{ $footerText }}</span>
            </div>
        @else
            <div class="fs-8 text-slate-500 font-medium text-center d-flex align-items-center justify-content-center gap-1">
                <i class="bi bi-check-circle text-emerald-500 opacity-75"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'مباشر ومُحدث' : 'Live Updated' }}</span>
            </div>
        @endif
    </div>
</div>

