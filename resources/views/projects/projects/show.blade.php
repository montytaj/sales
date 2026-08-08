<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('projects.index'), 'label' => __('projects.projects')],
                ['label' => $project->project_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-diagram-3-fill text-info me-2"></i>{{ $project->name }} ({{ $project->project_number }})
                </h2>
                <span class="badge bg-info-subtle text-info border border-info fs-6">
                    {{ __('projects.project_statuses.' . $project->status) }}
                </span>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addStageModal">
                    <i class="bi bi-plus-lg me-1"></i>إضافة مرحلة
                </button>
                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#addChangeOrderModal">
                    <i class="bi bi-file-earmark-plus me-1"></i>أمر تغيير
                </button>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="bi bi-cash me-1"></i>تسجيل مصروف / مقاول باطن
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Profitability & Analytics Dashboard Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card shadow-sm border-0 bg-primary-subtle text-primary h-100">
                <div class="card-body p-3">
                    <span class="d-block small text-uppercase font-bold mb-1">إجمالي الإيرادات المعدلة</span>
                    <h3 class="font-bold mb-0">{{ number_format($profitability['total_revenue'], 2) }} {{ setting('currency', 'SDG') }}</h3>
                    <small class="opacity-75">العقد الأساسي + أوامر التغيير المعتمدة</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card shadow-sm border-0 bg-danger-subtle text-danger h-100">
                <div class="card-body p-3">
                    <span class="d-block small text-uppercase font-bold mb-1">إجمالي المصروفات والباطن</span>
                    <h3 class="font-bold mb-0">{{ number_format($profitability['total_expenses'], 2) }} {{ setting('currency', 'SDG') }}</h3>
                    <small class="opacity-75">المواد + أجور العمالة + مقاولي الباطن</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card shadow-sm border-0 bg-success-subtle text-success h-100">
                <div class="card-body p-3">
                    <span class="d-block small text-uppercase font-bold mb-1">صافي ربحية المشروع</span>
                    <h3 class="font-bold mb-0">{{ number_format($profitability['net_profit'], 2) }} {{ setting('currency', 'SDG') }}</h3>
                    <small class="opacity-75">هامش الربح الحالي: {{ $profitability['profit_margin'] }}%</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card shadow-sm border-0 bg-dark text-white h-100">
                <div class="card-body p-3">
                    <span class="d-block small text-uppercase font-bold mb-1 text-light opacity-75">نسبة الإنجاز الإجمالية</span>
                    <h3 class="font-bold mb-1 text-warning">{{ $project->completion_percentage }}%</h3>
                    <div class="progress bg-secondary" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $project->completion_percentage }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stages & Progress -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-list-task me-2"></i>مراحل المشروع ونسب الإنجاز</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">المرحلة</th>
                            <th scope="col">الوزن النسبة (%)</th>
                            <th scope="col">نسبة إنجاز المرحلة</th>
                            <th scope="col">تاريخ الاستحقاق</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">تحديث الإنجاز</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($project->stages as $index => $stage)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td class="fw-semibold">{{ $stage->name }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $stage->weight_percentage }}%</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stage->completion_percentage }}%;"></div>
                                        </div>
                                        <small class="fw-bold">{{ $stage->completion_percentage }}%</small>
                                    </div>
                                </td>
                                <td>{{ $stage->due_date ? $stage->due_date->format('Y-m-d') : '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $stage->status }}</span></td>
                                <td class="text-end pe-3">
                                    <form method="POST" action="{{ route('projects.update-stage-progress', [$project, $stage]) }}" class="d-inline-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" min="0" max="100" name="completion_percentage" class="form-control form-control-sm" style="width: 70px;" value="{{ $stage->completion_percentage }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد مراحل مسجلة بالمشروع.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Change Orders & Expenses Grid -->
    <div class="row g-4 mb-4">
        <!-- Change Orders -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-file-earmark-plus me-2"></i>أوامر التغيير والملحقات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">رقم الأمر</th>
                                    <th scope="col">الأثر المالي</th>
                                    <th scope="col">الأثر الزمني</th>
                                    <th scope="col">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($project->changeOrders as $co)
                                    <tr>
                                        <td class="ps-3"><code>{{ $co->order_number }}</code></td>
                                        <td class="fw-bold text-success">+ {{ number_format($co->cost_impact, 2) }} {{ setting('currency', 'SDG') }}</td>
                                        <td>+ {{ $co->time_impact_days }} أيام</td>
                                        <td>
                                            @if ($co->status === 'approved')
                                                <span class="badge bg-success">معتمد</span>
                                            @else
                                                <form method="POST" action="{{ route('projects.approve-change-order', [$project, $co]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">اعتماد</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">لا توجد أوامر تغيير مسجلة.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-cash me-2"></i>سجل المصروفات ومقاولي الباطن</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">النوع</th>
                                    <th scope="col">الوصف</th>
                                    <th scope="col">المبلغ</th>
                                    <th scope="col">التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($project->expenses as $expense)
                                    <tr>
                                        <td class="ps-3"><span class="badge bg-secondary">{{ $expense->type }}</span></td>
                                        <td class="fw-semibold">{{ $expense->description }}</td>
                                        <td class="fw-bold text-danger">{{ number_format($expense->amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">لا توجد مصروفات مسجلة.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Stage Modal -->
    <div class="modal fade" id="addStageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('projects.add-stage', $project) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">إضافة مرحلة جديدة للمشروع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="stage_name" class="form-label font-semibold">اسم المرحلة <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="stage_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="weight_percentage" class="form-label font-semibold">الوزن النسبي (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="weight_percentage" id="weight_percentage" class="form-control" value="20" required>
                        </div>
                        <div class="mb-3">
                            <label for="stage_due_date" class="form-label font-semibold">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" id="stage_due_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ المرحلة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Change Order Modal -->
    <div class="modal fade" id="addChangeOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('projects.add-change-order', $project) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">تسجيل أمر تغيير للمشروع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="co_desc" class="form-label font-semibold">وصف التعديل أو التوسعة <span class="text-danger">*</span></label>
                            <textarea name="description" id="co_desc" rows="2" class="form-control" required></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="cost_impact" class="form-label font-semibold">الأثر المالي ({{ setting('currency', 'SDG') }}) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cost_impact" id="cost_impact" class="form-control" value="0" required>
                            </div>
                            <div class="col-6">
                                <label for="time_impact_days" class="form-label font-semibold">الأثر الزمني (أيام) <span class="text-danger">*</span></label>
                                <input type="number" name="time_impact_days" id="time_impact_days" class="form-control" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning">تسجيل أمر التغيير</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('projects.add-expense', $project) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">تسجيل مصروف / مقاول باطن</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="exp_type" class="form-label font-semibold">تصنيف المصروف <span class="text-danger">*</span></label>
                            <select name="type" id="exp_type" class="form-select" required>
                                <option value="material">مشتريات مواد ومواد خشب وخامات</option>
                                <option value="subcontractor">مقاول باطن</option>
                                <option value="labor">أجور وعمالة</option>
                                <option value="other">مصروف آخر</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exp_desc" class="form-label font-semibold">الوصف <span class="text-danger">*</span></label>
                            <input type="text" name="description" id="exp_desc" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="exp_amount" class="form-label font-semibold">المبلغ ({{ setting('currency', 'SDG') }}) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="exp_amount" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label for="expense_date" class="form-label font-semibold">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تسجيل المصروف وحسب الربحية</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
