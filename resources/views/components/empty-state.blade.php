@props([
    'icon' => 'bi-inbox-fill',
    'title' => __('general.no_data'),
    'description' => __('general.no_records_found'),
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => 'bi-plus-lg'
])

<div class="empty-state-card text-center py-5 px-4 my-4 card-custom border-dashed">
    <div class="empty-state-icon mx-auto mb-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
        <i class="bi {{ $icon }} fs-1 text-muted"></i>
    </div>
    
    <h3 class="h5 font-bold text-slate-800 mb-1">
        {{ $title }}
    </h3>
    
    <p class="text-muted fs-6 mb-4 max-w-md mx-auto">
        {{ $description }}
    </p>

    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary-custom shadow-sm">
            <i class="bi {{ $actionIcon }}"></i>
            <span>{{ $actionLabel }}</span>
        </a>
    @elseif(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
