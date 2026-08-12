<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-rulers text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إدارة وحدات القياس' : 'Units of Measurement' }}
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUnitModal">
                <i class="bi bi-plus-circle me-1"></i>إضافة وحدة جديدة
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
                            <th>الوحدة</th>
                            <th>الرمز</th>
                            <th>الأصناف</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-dark">{{ $unit->name }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary fs-7">{{ $unit->symbol ?? '-' }}</span></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $unit->base_items_count + $unit->wholesale_items_count }} صنف
                                    </span>
                                </td>
                                <td>
                                    @if($unit->is_active)
                                        <span class="badge bg-success-subtle text-success">مفعل</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">معطل</span>
                                    @endif
                                </td>
                                 <td class="text-nowrap">
                                     <div class="d-inline-flex align-items-center gap-1">
                                         <button type="button" class="btn btn-action-icon btn-action-edit" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#editUnitModal{{ $unit->id }}"
                                                 title="تعديل">
                                             <i class="bi bi-pencil"></i>
                                         </button>
                                         <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من الحذف؟')">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-action-icon btn-action-delete" title="حذف" {{ ($unit->base_items_count + $unit->wholesale_items_count) > 0 ? 'disabled' : '' }}>
                                                 <i class="bi bi-trash"></i>
                                             </button>
                                         </form>
                                     </div>

                                    <!-- Modal Edit Unit -->
                                    <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('units.update', $unit) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل وحدة قياس: {{ $unit->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label font-bold">اسم الوحدة <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $unit->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الرمز / الاختصار</label>
                                                            <input type="text" name="symbol" class="form-control" value="{{ $unit->symbol }}">
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeUnit{{ $unit->id }}" {{ $unit->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="activeUnit{{ $unit->id }}">حالة الوحدة (مفعلة)</label>
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
                                <td colspan="6" class="text-center text-muted py-4">لا توجد وحدات قياس مضافة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create Unit -->
    <div class="modal fade" id="createUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-bold"><i class="bi bi-plus-circle text-primary me-2"></i>إضافة وحدة قياس جديدة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">اسم الوحدة (مثال: قطعة، كرتونة، صندوق...) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="أدخل اسم الوحدة" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الرمز / الاختصار (مثال: قط، كرت، طرد)</label>
                            <input type="text" name="symbol" class="form-control" placeholder="اختصاري">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="createActiveUnit" checked>
                            <label class="form-check-label" for="createActiveUnit">تفعيل الوحدة فور الإضافة</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة الوحدة</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
