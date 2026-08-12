@props([
    'title' => null,
    'description' => null,
    'breadcrumbs' => null,
    'badge' => null,
    'badgeClass' => 'bg-primary-subtle text-primary border border-primary-subtle',
    'icon' => null
])

@php
    $isRtl = app()->getLocale() == 'ar';
    $circlePosition = $isRtl ? 'left: -10px;' : 'right: -10px;';
@endphp

<div class="page-header-card card border-0 shadow-sm rounded-3 px-3 px-md-4 py-2.5 py-md-2.5 mb-3 position-relative" style="z-index: 10;">
    <!-- Compact Semi-circle Backdrop Shape in Corner with Emerald Accent Gradient -->
    <div style="position: absolute; inset: 0; overflow: hidden; border-radius: inherit; pointer-events: none; z-index: 0;">
        <div style="position: absolute; top: -10px; {{ $circlePosition }} width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.10)); pointer-events: none;"></div>
    </div>

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 position-relative" style="z-index: 2;">
        <div class="page-header-info">
            @if($breadcrumbs)
                <div class="mb-1">
                    <x-breadcrumbs :items="$breadcrumbs" />
                </div>
            @endif
            
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="h5 font-bold text-slate-900 mb-0 tracking-tight d-flex align-items-center gap-2">
                    @if($icon)
                        <div class="rounded-2 bg-primary-subtle p-1 d-inline-flex align-items-center justify-content-center text-primary fs-6" style="width: 30px; height: 30px;">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                    @endif
                    <span>{{ $title ?? $slot }}</span>
                </h1>

                @if($badge)
                    <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill font-semibold fs-7 shadow-2xs">
                        {{ $badge }}
                    </span>
                @endif
            </div>

            @if($description)
                <p class="text-slate-500 fs-7 mb-0 mt-0.5 d-flex align-items-center gap-1.5 opacity-90">
                    <i class="bi bi-info-circle text-primary opacity-75 fs-8"></i>
                    <span>{{ $description }}</span>
                </p>
            @endif
        </div>

        @if(isset($actions))
            <div class="page-header-actions d-flex align-items-center gap-2 flex-wrap ms-auto">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
