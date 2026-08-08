<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-house-gear text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إدارة المخازن والمستودعات' : 'Warehouses Management' }}
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWarehouseModal">
                <i class="bi bi-plus-circle me-1"></i>إضافة مخزن جديد
            </button>
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>كود المخزن</th>
                            <th>اسم المخزن</th>
                            <th>أمين المخزن</th>
                            <th>الهاتف</th>
                            <th>الفرع التابع</th>
                            <th>عدد الأصناف المتوفرة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $wh)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary font-mono">{{ $wh->code }}</span></td>
                                <td class="fw-bold text-dark">
                                    <a href="{{ route('warehouses.show', $wh) }}" class="text-decoration-none text-primary">
                                        <i class="bi bi-box-seam me-1"></i>{{ $wh->name }}
                                    </a>
                                </td>
                                <td>{{ $wh->keeper_name ?? '-' }}</td>
                                <td><span class="dir-ltr text-start font-mono fs-7">{{ $wh->phone ?? '-' }}</span></td>
                                <td>{{ $wh->branch?->name ?? 'الفرع الرئيسي' }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fs-7">
                                        {{ $wh->warehouse_items_count }} صنف
                                    </span>
                                </td>
                                <td>
                                    @if($wh->is_active)
                                        <span class="badge bg-success-subtle text-success">نشط ومفعل</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('warehouses.show', $wh) }}" class="btn btn-sm btn-outline-info me-1" title="عرض مخزون المخزن">
                                        <i class="bi bi-eye"></i> عرض
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editWarehouseModal{{ $wh->id }}">
                                        <i class="bi bi-pencil"></i> تعديل
                                    </button>
                                    <form action="{{ route('warehouses.destroy', $wh) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من حذف هذا المخزن؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> حذف
                                        </button>
                                    </form>

                                    <!-- Modal Edit Warehouse -->
                                    <div class="modal fade" id="editWarehouseModal{{ $wh->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('warehouses.update', $wh) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-bold">تعديل المخزن: {{ $wh->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label font-bold">اسم المخزن <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $wh->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label font-bold">كود المخزن <span class="text-danger">*</span></label>
                                                            <input type="text" name="code" class="form-control" value="{{ $wh->code }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الفرع التابع</label>
                                                            <select name="branch_id" class="form-select">
                                                                <option value="">-- الفرع الرئيسي --</option>
                                                                @foreach($branches as $b)
                                                                    <option value="{{ $b->id }}" {{ $wh->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">أمين المخزن</label>
                                                                <input type="text" name="keeper_name" class="form-control" value="{{ $wh->keeper_name }}">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">رقم الهاتف</label>
                                                                <input type="text" name="phone" class="form-control" value="{{ $wh->phone }}">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">العنوان التفصيلي</label>
                                                            <input type="text" name="address" class="form-control" value="{{ $wh->address }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">ملاحظات المخزن</label>
                                                            <textarea name="notes" class="form-control" rows="2">{{ $wh->notes }}</textarea>
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeWh{{ $wh->id }}" {{ $wh->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="activeWh{{ $wh->id }}">حالة المخزن (نشط)</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">لا توجد مخازن مضافة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create Warehouse -->
    <div class="modal fade" id="createWarehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-bold"><i class="bi bi-plus-circle text-primary me-2"></i>إضافة مخزن جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">اسم المخزن <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="مثال: المستودع الرئيسي" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">كود المخزن <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="مثال: WH-01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الفرع التابع</label>
                            <select name="branch_id" class="form-select">
                                <option value="">-- الفرع الرئيسي --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم أمين المخزن</label>
                                <input type="text" name="keeper_name" class="form-control" placeholder="الاسم الكامل">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" placeholder="050xxxxxxx">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">العنوان التفصيلي للمخزن</label>
                            <input type="text" name="address" class="form-control" placeholder="المدينة - الحي - الشارع">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات عن طريقة التخزين أو الاستلام"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="createActiveWh" checked>
                            <label class="form-check-label" for="createActiveWh">تفعيل المخزن فور الإضافة</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة المخزن</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
