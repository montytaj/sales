@props(['items' => []])

@if (!empty($items))
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1.5 fs-7 bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-primary text-decoration-none font-medium opacity-85 hover-opacity-100 transition-all">
                    <i class="bi bi-house-door me-1"></i>{{ __('general.home') }}
                </a>
            </li>
            @foreach ($items as $item)
                @if (isset($item['url']) && !$loop->last)
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}" class="text-primary text-decoration-none font-medium opacity-85 hover-opacity-100 transition-all">{{ $item['label'] }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item active text-slate-500 font-semibold" aria-current="page">{{ $item['label'] }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
