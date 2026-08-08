@props([
    'status',
    'label' => null
])

@php
    $key = strtolower(trim((string)$status));
    $isAr = app()->getLocale() == 'ar';

    $statusConfig = [
        // Work Order & Project Statuses
        'pending' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'border' => 'border-warning-subtle', 'icon' => 'bi-clock-history', 'default_label' => $isAr ? 'بانتظار الإجراء' : 'Pending'],
        'authorized' => ['bg' => 'bg-info-subtle', 'text' => 'text-info-emphasis', 'border' => 'border-info-subtle', 'icon' => 'bi-shield-check', 'default_label' => $isAr ? 'مصرح للبدء' : 'Authorized'],
        'in_progress' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary-emphasis', 'border' => 'border-primary-subtle', 'icon' => 'bi-gear-wide-connected', 'default_label' => $isAr ? 'قيد التنفيذ' : 'In Progress'],
        'completed' => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'icon' => 'bi-check-circle-fill', 'default_label' => $isAr ? 'مكتمل' : 'Completed'],
        'delivered' => ['bg' => 'bg-emerald-subtle', 'text' => 'text-emerald-emphasis', 'border' => 'border-emerald-subtle', 'icon' => 'bi-box-seam', 'default_label' => $isAr ? 'تم التسليم' : 'Delivered'],
        'stopped' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'border' => 'border-danger-subtle', 'icon' => 'bi-pause-circle-fill', 'default_label' => $isAr ? 'متوقف' : 'Stopped'],
        'delayed' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'border' => 'border-danger-subtle', 'icon' => 'bi-alarm-fill', 'default_label' => $isAr ? 'متأخر' : 'Delayed'],
        'overdue' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'border' => 'border-danger-subtle', 'icon' => 'bi-exclamation-triangle-fill', 'default_label' => $isAr ? 'متأخر جداً' : 'Overdue'],

        // Financial & Invoice Statuses
        'draft' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary-emphasis', 'border' => 'border-secondary-subtle', 'icon' => 'bi-file-earmark-code', 'default_label' => $isAr ? 'مسودة' : 'Draft'],
        'approved' => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'icon' => 'bi-patch-check-fill', 'default_label' => $isAr ? 'معتمد' : 'Approved'],
        'rejected' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'border' => 'border-danger-subtle', 'icon' => 'bi-x-circle-fill', 'default_label' => $isAr ? 'مرفوض' : 'Rejected'],
        'paid' => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'icon' => 'bi-cash-stack', 'default_label' => $isAr ? 'مسدد بالكامل' : 'Paid'],
        'partial' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'border' => 'border-warning-subtle', 'icon' => 'bi-pie-chart-fill', 'default_label' => $isAr ? 'مسدد جزئياً' : 'Partially Paid'],
        'partially_paid' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'border' => 'border-warning-subtle', 'icon' => 'bi-pie-chart-fill', 'default_label' => $isAr ? 'مسدد جزئياً' : 'Partially Paid'],
        'unpaid' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger-emphasis', 'border' => 'border-danger-subtle', 'icon' => 'bi-exclamation-circle-fill', 'default_label' => $isAr ? 'غير مسدد' : 'Unpaid'],
        'cancelled' => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark-emphasis', 'border' => 'border-dark-subtle', 'icon' => 'bi-slash-circle', 'default_label' => $isAr ? 'ملغى' : 'Cancelled'],
        'active' => ['bg' => 'bg-success-subtle', 'text' => 'text-success-emphasis', 'border' => 'border-success-subtle', 'icon' => 'bi-check-lg', 'default_label' => $isAr ? 'نشط' : 'Active'],
        'inactive' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary-emphasis', 'border' => 'border-secondary-subtle', 'icon' => 'bi-x-lg', 'default_label' => $isAr ? 'غير نشط' : 'Inactive'],
    ];

    $cfg = $statusConfig[$key] ?? [
        'bg' => 'bg-secondary-subtle',
        'text' => 'text-secondary-emphasis',
        'border' => 'border-secondary-subtle',
        'icon' => 'bi-info-circle',
        'default_label' => $isAr ? str_replace('_', ' ', $key) : ucfirst(str_replace('_', ' ', $key))
    ];

    $displayLabel = $label ?? $cfg['default_label'];
@endphp

<span class="badge {{ $cfg['bg'] }} {{ $cfg['text'] }} border {{ $cfg['border'] }} px-2.5 py-1 rounded-pill font-medium fs-7 d-inline-flex align-items-center gap-1.5 shadow-2xs">
    <i class="bi {{ $cfg['icon'] }} fs-8"></i>
    <span>{{ $displayLabel }}</span>
</span>
