<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-shield-check text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'سجل تتبع الحركات والرقابة' : 'Audit Trail & Activity Log' }}
                </h2>
                <p class="text-muted fs-7 mb-0">{{ app()->getLocale() == 'ar' ? 'توثيق وتتبع كافة عمليات الإضافة والتعديل والحذف وتغييرات الأسعار بالنظام' : 'Track all system changes, price updates, and modifications' }}</p>
            </div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-7 rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2 shadow-2xs">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="font-medium">{{ app()->getLocale() == 'ar' ? 'تتبع فوري ومباشر' : 'Real-time Audit' }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Top KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'إجمالي حركات الرقابة المسجلة' : 'Total Logged Actions'"
                :value="number_format($logs->total())"
                :subtitle="app()->getLocale() == 'ar' ? 'سجل رقابي موثق' : 'Logged events'"
                icon="bi-journal-text"
                color="primary" />
        </div>

        <div class="col-12 col-md-4">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'حركات اليوم' : 'Today Actions'"
                :value="number_format(\App\Models\ActivityLog::whereDate('created_at', now())->count())"
                :subtitle="app()->getLocale() == 'ar' ? 'عمليات جارية اليوم' : 'Today events'"
                icon="bi-clock-history"
                color="emerald" />
        </div>

        <div class="col-12 col-md-4">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'المستخدمون النشطون' : 'Active Users'"
                :value="number_format($users->count())"
                :subtitle="app()->getLocale() == 'ar' ? 'مستخدم بصلاحيات تتبع' : 'Active users'"
                icon="bi-people-fill"
                color="purple" />
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white overflow-hidden">
        <form action="{{ route('activity-logs.index') }}" method="GET">
            <div class="row g-2.5 align-items-end">
                <div class="col-12 col-lg-3 col-md-6">
                    <label class="form-label font-bold fs-7 text-slate-700 mb-1">
                        <i class="bi bi-search me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'البحث بالنظام' : 'Search' }}
                    </label>
                    <input type="text" name="search" class="form-control fs-7 rounded-3" value="{{ request('search') }}" placeholder="{{ app()->getLocale() == 'ar' ? 'اسم الحركة، الوصف أو IP...' : 'Search action, desc, IP...' }}">
                </div>

                <div class="col-12 col-lg-3 col-md-6">
                    <label class="form-label font-bold fs-7 text-slate-700 mb-1">
                        <i class="bi bi-person me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'تصفية حسب المستخدم' : 'Filter by User' }}
                    </label>
                    <select name="user_id" class="form-select fs-7 rounded-3">
                        <option value="">-- {{ app()->getLocale() == 'ar' ? 'جميع المستخدمين' : 'All Users' }} --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2 col-md-3">
                    <label class="form-label font-bold fs-7 text-slate-700 mb-1">{{ app()->getLocale() == 'ar' ? 'من تاريخ' : 'From Date' }}</label>
                    <input type="date" name="from_date" class="form-control fs-7 rounded-3" value="{{ request('from_date') }}">
                </div>

                <div class="col-6 col-lg-2 col-md-3">
                    <label class="form-label font-bold fs-7 text-slate-700 mb-1">{{ app()->getLocale() == 'ar' ? 'إلى تاريخ' : 'To Date' }}</label>
                    <input type="date" name="to_date" class="form-control fs-7 rounded-3" value="{{ request('to_date') }}">
                </div>

                <div class="col-12 col-lg-2 col-md-6 mt-2 mt-lg-0">
                    <div class="d-flex align-items-center gap-2 w-100">
                        <button type="submit" class="btn btn-primary font-bold fs-7 rounded-3 py-2 px-3 flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-2xs text-nowrap">
                            <i class="bi bi-funnel-fill"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'تصفية' : 'Filter' }}</span>
                        </button>
                        @if(request()->anyFilled(['search', 'user_id', 'from_date', 'to_date']))
                            <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-danger font-bold fs-7 rounded-3 py-2 px-2.5 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap shadow-2xs" title="{{ app()->getLocale() == 'ar' ? 'إعادة ضبط الفلاتر' : 'Reset Filters' }}">
                                <i class="bi bi-arrow-counterclockwise fs-6"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Reset' }}</span>
                            </a>
                        @else
                            <a href="{{ route('activity-logs.index') }}" class="btn btn-light border text-slate-600 font-medium fs-7 rounded-3 py-2 px-2.5 d-inline-flex align-items-center justify-content-center shadow-2xs text-nowrap" title="{{ app()->getLocale() == 'ar' ? 'إعادة ضبط الفلاتر' : 'Reset Filters' }}">
                                <i class="bi bi-arrow-counterclockwise fs-6"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Activity Log Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-slate-900 mb-0 fs-6">
                <i class="bi bi-list-check text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'سجل العمليات والأنشطة الرقمية' : 'System Audit Log' }}
            </h5>
            <span class="badge bg-slate-100 text-slate-700 font-mono fs-8 rounded-pill px-3 py-1">
                {{ app()->getLocale() == 'ar' ? 'عرض ' : 'Showing ' }}{{ $logs->count() }}{{ app()->getLocale() == 'ar' ? ' من أصل ' : ' of ' }}{{ $logs->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light fs-7">
                        <tr>
                            <th scope="col" class="ps-4">#</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'التاريخ والوقت' : 'Timestamp' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'المستخدم' : 'User' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'نوع الحركة' : 'Action' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'تفاصيل الحركة والكيان' : 'Description' }}</th>
                            <th scope="col">{{ app()->getLocale() == 'ar' ? 'عنوان IP' : 'IP Address' }}</th>
                            <th scope="col" class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'التفاصيل' : 'Details' }}</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        @forelse($logs as $log)
                            @php
                                $actionName = strtolower($log->action);
                                $isCreate = str_contains($actionName, 'created');
                                $isUpdate = str_contains($actionName, 'updated');
                                $isDelete = str_contains($actionName, 'deleted');

                                $badgeClass = $isCreate ? 'bg-success-subtle text-success border-success-subtle' 
                                            : ($isUpdate ? 'bg-primary-subtle text-primary border-primary-subtle' 
                                            : ($isDelete ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-slate-100 text-slate-700'));
                            @endphp
                            <tr>
                                <td class="ps-4 font-mono text-slate-500">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td>
                                    <div class="font-mono fs-7 fw-bold text-slate-900">{{ $log->created_at->format('Y-m-d') }}</div>
                                    <small class="font-mono text-muted fs-8">{{ $log->created_at->format('H:i:s A') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold p-1.5 d-flex align-items-center justify-content-center font-mono fs-8" style="width: 30px; height: 30px;">
                                            {{ strtoupper(substr($log->user?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="d-block text-slate-800 fs-7">{{ $log->user?->name ?? (app()->getLocale() == 'ar' ? 'النظام / تلقائي' : 'System Auto') }}</strong>
                                            <small class="text-muted fs-8">{{ $log->user?->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge border {{ $badgeClass }} px-2.5 py-1 font-mono rounded-pill fs-8">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="fw-medium text-slate-800">{{ $log->description ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-600 font-mono fs-8 px-2 py-1">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-3 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#logDetailsModal{{ $log->id }}">
                                        <i class="bi bi-eye me-1"></i>{{ app()->getLocale() == 'ar' ? 'عرض التغييرات' : 'View Diff' }}
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal: Log Details & Diff Inspection -->
                            <div class="modal fade" id="logDetailsModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content rounded-4 border-0 shadow-lg">
                                        <div class="modal-header bg-light border-bottom p-3">
                                            <h5 class="modal-title font-bold fs-6 text-slate-900">
                                                <i class="bi bi-info-circle me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'تفاصيل الحركة والتغييرات #' : 'Audit Log Details #' }}{{ $log->id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-3">
                                                <div class="col-6 col-md-3">
                                                    <small class="text-muted d-block fs-8">{{ app()->getLocale() == 'ar' ? 'المستخدم:' : 'User:' }}</small>
                                                    <strong class="fs-7 text-slate-900">{{ $log->user?->name ?? 'System' }}</strong>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <small class="text-muted d-block fs-8">{{ app()->getLocale() == 'ar' ? 'تاريخ الحركة:' : 'Date:' }}</small>
                                                    <strong class="font-mono fs-7 text-slate-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</strong>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <small class="text-muted d-block fs-8">{{ app()->getLocale() == 'ar' ? 'نوع الكيان:' : 'Subject:' }}</small>
                                                    <strong class="font-mono fs-8 text-slate-700 text-truncate d-block">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</strong>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <small class="text-muted d-block fs-8">{{ app()->getLocale() == 'ar' ? 'IP Address:' : 'IP Address:' }}</small>
                                                    <strong class="font-mono fs-7 text-slate-900">{{ $log->ip_address }}</strong>
                                                </div>
                                            </div>

                                            @if(isset($log->properties['changes']) && is_array($log->properties['changes']))
                                                <h6 class="fw-bold text-slate-800 fs-7 mb-2 border-bottom pb-2">
                                                    <i class="bi bi-arrow-left-right text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'جدول التغييرات المقارنة (قبل وبعد التعديل):' : 'Changed Attributes Diff:' }}
                                                </h6>
                                                <div class="table-responsive bg-light p-2 rounded-3 mb-3">
                                                    <table class="table table-sm table-bordered mb-0 fs-8 bg-white">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>الحقل (Field)</th>
                                                                <th class="bg-danger text-white">القيمة السابقة (Old)</th>
                                                                <th class="bg-success text-white">القيمة الجديدة (New)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($log->properties['changes'] as $field => $diff)
                                                                <tr>
                                                                    <td class="fw-bold font-mono text-slate-800">{{ $field }}</td>
                                                                    <td class="text-danger font-mono bg-danger-subtle">{{ is_array($diff['old']) ? json_encode($diff['old']) : ($diff['old'] ?? 'NULL') }}</td>
                                                                    <td class="text-success font-mono bg-success-subtle">{{ is_array($diff['new']) ? json_encode($diff['new']) : ($diff['new'] ?? 'NULL') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            @if(isset($log->properties['attributes']) && is_array($log->properties['attributes']))
                                                <h6 class="fw-bold text-slate-800 fs-7 mb-2">
                                                    <i class="bi bi-code-square text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'البيانات المسجلة (Payload Raw Attributes):' : 'Raw Attributes:' }}
                                                </h6>
                                                <pre class="bg-dark text-emerald-400 p-3 rounded-3 font-mono fs-8 mb-0 custom-scrollbar" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        </div>
                                        <div class="modal-footer bg-light p-3">
                                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">{{ app()->getLocale() == 'ar' ? 'إغلاق' : 'Close' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ app()->getLocale() == 'ar' ? 'لم يتم تسجيل أي حركات رقابية بعد' : 'No audit log records found' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
