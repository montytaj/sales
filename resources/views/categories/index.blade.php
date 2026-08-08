<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-tags text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إدارة تصنيفات الأصناف' : 'Item Categories' }}
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle me-1"></i>إضافة تصنيف جديد
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
                            <th>كود التصنيف</th>
                            <th>اسم التصنيف</th>
                            <th>التصنيف الأب</th>
                            <th>عدد الأصناف</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary font-mono">{{ $category->code ?? '-' }}</span></td>
                                <td class="fw-bold text-dark">{{ $category->name }}</td>
                                <td>
                                    @if($category->parent)
                                        <span class="badge bg-info-subtle text-info"><i class="bi bi-diagram-2 me-1"></i>{{ $category->parent->name }}</span>
                                    @else
                                        <span class="text-muted fs-7">تصنيف رئيسي</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $category->items_count }} صنف
                                    </span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-subtle text-success">مفعل</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCategoryModal{{ $category->id }}">
                                        <i class="bi bi-pencil"></i> تعديل
                                    </button>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من حذف هذا التصنيف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" {{ $category->items_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i> حذف
                                        </button>
                                    </form>

                                    <!-- Modal Edit Category -->
                                    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('categories.update', $category) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-bold">تعديل التصنيف: {{ $category->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label font-bold">اسم التصنيف <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">كود التصنيف</label>
                                                            <input type="text" name="code" class="form-control" value="{{ $category->code }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">التصنيف الأب (اختر إذا كان تصنيف فرعي)</label>
                                                            <select name="parent_id" class="form-select">
                                                                <option value="">-- تصنيف رئيسي (بدون أب) --</option>
                                                                @foreach($parentCategories as $pCat)
                                                                    @if($pCat->id != $category->id)
                                                                        <option value="{{ $pCat->id }}" {{ $category->parent_id == $pCat->id ? 'selected' : '' }}>{{ $pCat->name }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">الوصف</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="activeCat{{ $category->id }}" {{ $category->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="activeCat{{ $category->id }}">حالة التصنيف (مفعل)</label>
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
                                <td colspan="7" class="text-center text-muted py-4">لا توجد تصنيفات مضافة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create Category -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-bold"><i class="bi bi-plus-circle text-primary me-2"></i>إضافة تصنيف أصناف جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">اسم التصنيف <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="أدخل اسم التصنيف" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كود التصنيف (اختياري)</label>
                            <input type="text" name="code" class="form-control" placeholder="مثال: CAT-FOOD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">التصنيف الأب</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- تصنيف رئيسي (بدون أب) --</option>
                                @foreach($parentCategories as $pCat)
                                    <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملاحظات / وصف التصنيف</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="تفاصيل التصنيف"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="createActiveCat" checked>
                            <label class="form-check-label" for="createActiveCat">تفعيل التصنيف فور الإضافة</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة التصنيف</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
