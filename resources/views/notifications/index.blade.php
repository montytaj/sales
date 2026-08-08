<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => 'مركز الإشعارات والأنشطة']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-bell-fill text-primary me-2"></i>مركز الإشعارات والتنبيهات
            </h2>
            <form method="POST" action="{{ route('notifications.mark-all-as-read') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-check-all me-1"></i>تحديد الكل كمقروء
                </button>
            </form>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <select name="priority" class="form-select">
                        <option value="">-- كافة مستويات الأولوية --</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>عاجل جداً (Urgent)</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>عالية (High)</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>عادية (Normal)</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>منخفضة (Low)</option>
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <input type="text" name="type" class="form-control" placeholder="نوع الإشعار (work_order, invoice, payment...)" value="{{ request('type') }}">
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['priority', 'type']))
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse ($notifications as $notification)
                    <div class="list-group-item p-3 {{ !$notification->is_read ? 'bg-primary-subtle' : '' }} d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-start gap-3">
                            <div class="p-2 rounded-circle {{ $notification->priority === 'urgent' ? 'bg-danger text-white' : ($notification->priority === 'high' ? 'bg-warning text-dark' : 'bg-primary text-white') }}">
                                <i class="bi bi-bell-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-dark mb-1">{{ $notification->title }}</h6>
                                <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            @if ($notification->action_url)
                                <a href="{{ route('notifications.mark-as-read', $notification) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>انتقال
                                </a>
                            @endif

                            @if (!$notification->is_read)
                                <form method="POST" action="{{ route('notifications.mark-as-read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-check me-1"></i>تحديد كمقروء
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
                        لا توجد إشعارات حالياً.
                    </div>
                @endforelse
            </div>
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
