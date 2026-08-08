<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('projects.signage')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-easel2-fill text-warning me-2"></i>{{ __('projects.signage') }}
            </h2>
            @can('create-signage')
                <a href="{{ route('signage-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('projects.create_signage') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('signage-orders.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم اللافتة، المقاسات، أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة حالات اللافتات --</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>جديد</option>
                        <option value="design_phase" {{ request('status') === 'design_phase' ? 'selected' : '' }}>مرحلة التصميم</option>
                        <option value="manufacturing" {{ request('status') === 'manufacturing' ? 'selected' : '' }}>التصنيع</option>
                        <option value="installation" {{ request('status') === 'installation' ? 'selected' : '' }}>التركيب</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('signage-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">رقم الطلب</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">الربط بمشروع</th>
                            <th scope="col">المقاسات</th>
                            <th scope="col">اعتماد التصميم</th>
                            <th scope="col">التصنيع والتركيب</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($signageOrders as $signage)
                            <tr>
                                <td class="ps-3"><code>{{ $signage->order_number }}</code></td>
                                <td class="fw-semibold">{{ $signage->customer->name }}</td>
                                <td>
                                    @if ($signage->project)
                                        <span class="badge bg-info-subtle text-info me-1"><i class="bi bi-diagram-3 me-1"></i>{{ $signage->project->project_number }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border">طلب مستقل</span>
                                    @endif
                                </td>
                                <td>{{ $signage->dimensions }}</td>
                                <td>
                                    @if ($signage->design_approved)
                                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle me-1"></i>تصميم معتمد</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-hourglass-split me-1"></i>بانتظار الاعتماد</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="d-block">تصنيع: {{ $signage->manufacturing_status }}</small>
                                    <small class="text-muted">تركيب: {{ $signage->installation_status }}</small>
                                </td>
                                <td><span class="badge bg-primary">{{ $signage->status }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('signage-orders.show', $signage) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد طلبات لافتات إعلانية مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($signageOrders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $signageOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
