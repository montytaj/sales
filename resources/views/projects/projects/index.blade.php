<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('projects.projects')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-diagram-3-fill text-info me-2"></i>{{ __('projects.projects') }}
            </h2>
            @can('create-projects')
                <a href="{{ route('projects.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('projects.create_project') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم المشروع، اسم المشروع، أو العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة حالات المشاريع --</option>
                        @foreach (__('projects.project_statuses') as $pKey => $pName)
                            <option value="{{ $pKey }}" {{ request('status') === $pKey ? 'selected' : '' }}>{{ $pName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('projects.project_number') }}</th>
                            <th scope="col">المشروع</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">الميزانية</th>
                            <th scope="col">الإنجاز</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td class="ps-3"><code>{{ $project->project_number }}</code></td>
                                <td class="fw-semibold">{{ $project->name }}</td>
                                <td>{{ $project->customer->name }}</td>
                                <td class="fw-bold text-dark">{{ number_format($project->budget, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $project->completion_percentage }}%;"></div>
                                        </div>
                                        <small class="fw-bold">{{ $project->completion_percentage }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info">
                                        {{ __('projects.project_statuses.' . $project->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-action-icon btn-action-show" title="لوحة التحكم والربحية (عرض)">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد مشاريع قائمة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($projects->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
