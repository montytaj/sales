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
    'actionText' => null
])

@php
    $displayDate = $date ?? \Carbon\Carbon::now()->format('Y-m-d');
    $isRtl = app()->getLocale() == 'ar';

    $colors = [
        'primary'   => ['border' => '#2563eb', 'bg' => 'rgba(37, 99, 235, 0.08)',  'icon_bg' => 'rgba(37, 99, 235, 0.15)',  'text' => '#2563eb'],
        'success'   => ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.08)', 'icon_bg' => 'rgba(16, 185, 129, 0.15)', 'text' => '#059669'],
        'emerald'   => ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.08)', 'icon_bg' => 'rgba(16, 185, 129, 0.15)', 'text' => '#059669'],
        'danger'    => ['border' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.08)',  'icon_bg' => 'rgba(239, 68, 68, 0.15)',  'text' => '#dc2626'],
        'warning'   => ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.08)', 'icon_bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#d97706'],
        'amber'     => ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.08)', 'icon_bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#d97706'],
        'info'      => ['border' => '#0284c7', 'bg' => 'rgba(2, 132, 199, 0.08)',  'icon_bg' => 'rgba(2, 132, 199, 0.15)',  'text' => '#0284c7'],
        'purple'    => ['border' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.08)', 'icon_bg' => 'rgba(139, 92, 246, 0.15)', 'text' => '#7c3aed'],
        'slate'     => ['border' => '#64748b', 'bg' => 'rgba(100, 116, 139, 0.08)','icon_bg' => 'rgba(100, 116, 139, 0.15)','text' => '#475569'],
        'secondary' => ['border' => '#64748b', 'bg' => 'rgba(100, 116, 139, 0.08)','icon_bg' => 'rgba(100, 116, 139, 0.15)','text' => '#475569'],
    ];

    $c = $colors[$color] ?? $colors['primary'];

    // Position single small corner backdrop shape behind the icon
    $circlePosition = $isRtl ? 'left: -12px;' : 'right: -12px;';
@endphp

<div class="card border-0 shadow-sm rounded-4 bg-white hover-lift transition h-100 position-relative overflow-hidden {{ $url ? 'card-clickable' : '' }}" 
     style="border-top: 4px solid {{ $c['border'] }} !important; min-height: 165px;">

    <!-- Single Compact Corner Backdrop Shape Behind Icon -->
    <div style="position: absolute; top: -12px; {{ $circlePosition }} width: 75px; height: 75px; border-radius: 50%; background: {{ $c['bg'] }}; pointer-events: none; z-index: 0;"></div>

    @if($url)
        <a href="{{ $url }}" class="text-decoration-none text-reset h-100 d-flex flex-column justify-content-between p-3 position-relative z-1">
    @else
        <div class="card-body p-3 d-flex flex-column justify-content-between h-100 position-relative z-1">
    @endif

        <!-- Card Top Row: Date Badge & Single Semi-Circular Theme Icon -->
        <div class="d-flex align-items-center justify-content-between mb-2 w-100">
            <span class="badge bg-slate-100 text-slate-600 font-mono fs-8 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 border border-slate-200/60 shadow-2xs">
                <i class="bi bi-clock opacity-75"></i>
                <span class="dir-ltr">{{ $displayDate }}</span>
            </span>

            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-2xs" 
                 style="width: 38px; height: 38px; background: {{ $c['icon_bg'] }}; color: {{ $c['text'] }}; flex-shrink: 0;">
                <i class="bi {{ $icon }} fs-6"></i>
            </div>
        </div>

        <!-- Card Middle: Centered Value & Currency -->
        <div class="text-center my-auto py-1">
            <div class="fw-black text-slate-900 mb-0.5 tracking-tight font-mono d-flex align-items-center justify-content-center gap-1.5 flex-wrap" style="font-size: 1.45rem; line-height: 1.15;">
                <span>{{ $value }}</span>
                @if($currency)
                    <span class="badge bg-primary-subtle text-primary font-sans font-bold fs-8 px-2 py-0.5 rounded-pill border border-primary-subtle">
                        {{ $currency }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Card Bottom Row: Title Label & Subtitle -->
        <div class="text-center mt-1">
            <h6 class="fs-7 font-bold text-slate-800 mb-0 text-center">
                {{ $title }}
            </h6>
            @if($subtitle)
                <div class="fs-8 text-slate-500 text-center font-medium">{{ $subtitle }}</div>
            @endif
        </div>

        <!-- Action Link Button (if url is passed) -->
        @if($url)
            <div class="pt-2 mt-2 border-top border-slate-100">
                <span class="btn btn-xs btn-primary-custom w-100 py-1 fs-8 font-bold d-inline-flex align-items-center justify-content-center gap-1.5 rounded-pill shadow-2xs">
                    <span>{{ $actionText ?? 'عرض التفاصيل' }}</span>
                    <i class="bi bi-arrow-left fs-8"></i>
                </span>
            </div>
        @endif

    @if($url)
        </a>
    @else
        </div>
    @endif
</div>
