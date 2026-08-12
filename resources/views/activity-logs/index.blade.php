<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => 'سجل النشاطات والأحداث']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-clock-history text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'سجل النشاطات والرقابة' : 'Activity Logs & Audit Trail' }}
                </h2>
                <p class="text-muted fs-7 mb-0">
                    استعراض كافة الحركات والتغيرات الحساسة التي تمت بالنظام وتتبع المستخدمين والتاريخ والأحداث
                </p>
            </div>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i>سجل تدقيق آمن وغير قابل للتعديل
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="search" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                        <i class="bi bi-search me-1 text-primary"></i>بحث في الوصف والـ IP
                    </label>
                    <input type="text" name="search" id="search" class="form-control fs-7 py-2 rounded-3" placeholder="ابحث بالنص..." value="{{ request('search') }}">
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label for="user_id" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                        <i class="bi bi-person me-1 text-primary"></i>المستخدم
                    </label>
                    <select name="user_id" id="user_id" class="form-select fs-7 py-2 rounded-3">
                        <option value="">-- جميع المستخدمين --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <label for="from_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" id="from_date" class="form-control fs-7 py-2 rounded-3" value="{{ request('from_date') }}">
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <label for="to_date" class="form-label font-semibold fs-7 text-slate-700 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" id="to_date" class="form-control fs-7 py-2 rounded-3" value="{{ request('to_date') }}">
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary font-bold w-100 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-filter me-1"></i>تصفية
                    </button>
                    @if (request()->hasAny(['search', 'user_id', 'action', 'from_date', 'to_date']))
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary py-2 rounded-3" title="تصفية الكل">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-slate-50 border-bottom border-slate-200">
                        <tr>
                            <th scope="col" class="ps-4" style="width: 60px;">#</th>
                            <th scope="col">المستخدم</th>
                            <th scope="col">الحدث / الإجراء</th>
                            <th scope="col">التفاصيل والوصف</th>
                            <th scope="col">عنوان IP</th>
                            <th scope="col">التاريخ والتوقيت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="ps-4 text-muted fs-8"><code>#{{ $log->id }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center fw-bold fs-7" style="width: 34px; height: 34px;">
                                            {{ mb_substr($log->user?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-900 fs-7">{{ $log->user?->name ?? 'النظام / تلقائي' }}</div>
                                            <small class="text-muted fs-8">{{ $log->user?->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $act = strtolower($log->action);
                                        $badgeClass = 'bg-secondary-subtle text-secondary border-secondary';
                                        if (str_contains($act, 'create') || str_contains($act, 'store') || str_contains($act, 'add')) {
                                            $badgeClass = 'bg-success-subtle text-success border-success';
                                        } elseif (str_contains($act, 'update') || str_contains($act, 'edit')) {
                                            $badgeClass = 'bg-info-subtle text-info border-info';
                                        } elseif (str_contains($act, 'delete') || str_contains($act, 'cancel') || str_contains($act, 'destroy')) {
                                            $badgeClass = 'bg-danger-subtle text-danger border-danger';
                                        } elseif (str_contains($act, 'post')) {
                                            $badgeClass = 'bg-primary-subtle text-primary border-primary';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} border px-2.5 py-1 rounded-pill font-mono fs-8">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-slate-800 fs-7">{{ $log->description ?? '-' }}</span>
                                    @if ($log->subject_type)
                                        <small class="d-block text-muted fs-8 font-mono">
                                            الكيان: {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <code class="fs-8 text-slate-600 bg-slate-100 px-2 py-0.5 rounded border">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td>
                                    <div class="fs-7 text-slate-800 fw-semibold">{{ $log->created_at?->format('Y-m-d H:i:s') }}</div>
                                    <small class="text-muted fs-8"><i class="bi bi-clock me-1"></i>{{ $log->created_at?->diffForHumans() }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2 text-slate-300"></i>
                                    لا توجد سجلات نشاط مسجلة حالياً تطابق معايير البحث.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
