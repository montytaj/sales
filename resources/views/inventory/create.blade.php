<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-plus-circle text-primary me-2"></i>إضافة صنف جديد وحدات متعددة (جملة وفرادي)
            </h2>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>الرجوع للأصناف
            </a>
        </div>
    </x-slot>

    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Basic Details -->
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>البيانات الأساسية للصنف</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-bold">اسم الصنف <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="مثال: زيت طعام عافية 1.5 لتر" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-bold">التصنيف الرئيسي <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- اختر التصنيف --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كود الصنف (Item Code)</label>
                                <input type="text" name="code" class="form-control" placeholder="تلقائي أو أدخل كود">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الباركوم (Barcode)</label>
                                <input type="text" name="barcode" class="form-control" placeholder="مسح أو إدخال الرقم">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">وصف وتفاصيل الصنف</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="تفاصيل ومواصفات المنتج"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Units & Pricing Row -->
            <div class="col-12 col-lg-6">
                <!-- Units & Multi-Unit Setup -->
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-rulers text-primary me-2"></i>تحديد وحدات القياس ومُعامل التحويل</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label font-bold">الوحدة الصغرى / الفرادي (Base Unit) <span class="text-danger">*</span></label>
                                <select name="base_unit_id" class="form-select" required>
                                    <option value="">-- اختر الوحدة الصغرى --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">القطعة / العلبة / الكيلو</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-bold">الوحدة الكبرى / الجملة (Wholesale Unit)</label>
                                <select name="wholesale_unit_id" class="form-select">
                                    <option value="">-- لا يوجد (بيع فرادي فقط) --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">الكرتونة / الصندوق / الطرد</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-bold">معامل التحويل (عدد الوحدات الصغرى) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="conversion_factor" class="form-control" value="10" required>
                                <small class="text-muted d-block mt-1">مثال: 1 كرتونة تحتوي على 10 قطع</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <!-- Prices -->
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-currency-dollar text-primary me-2"></i>تسعير الصنف (التكلفة، الجملة، التجزئة)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label font-bold">سعر التكلفة (للوحدة الصغرى) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="0.00" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-bold">سعر بيع الجملة (للكرتونة) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="wholesale_price" class="form-control" value="0.00" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label font-bold">سعر بيع القطاعي/الفرادي (للقطعة) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="retail_price" class="form-control" value="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Initial Stock (Opening Balance) Section at Bottom -->
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0 font-bold text-dark">
                            <i class="bi bi-box-arrow-in-down-left text-primary me-2"></i>بضاعة أول المدة (المخزون الافتتاحي)
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('warehouses.index') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-bold fs-7">
                                <i class="bi bi-plus-circle me-1"></i>+ إضافة مخزن جديد
                            </a>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-8 font-medium">
                                توزيع كميات الأصناف على المخازن
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="fs-7 text-muted mb-3">أدخل كميات بضاعة أول المدة لكل مخزن (بالوحدة الكبرى والفرادي):</p>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                            @foreach($warehouses as $wh)
                                <div class="col">
                                    <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 h-100">
                                        <div class="font-bold text-slate-900 fs-7 mb-2.5 d-flex align-items-center gap-2">
                                            <i class="bi bi-building text-primary"></i>
                                            <span>{{ $wh->name }}</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fs-8 text-muted mb-1 font-medium">وحدة كبرى (جملة)</label>
                                                <input type="number" step="0.01" min="0" name="initial_stock[{{ $wh->id }}][wholesale]" class="form-control form-control-sm font-mono" value="0" placeholder="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fs-8 text-muted mb-1 font-medium">وحدة صغرى (فرادي)</label>
                                                <input type="number" step="0.01" min="0" name="initial_stock[{{ $wh->id }}][base]" class="form-control form-control-sm font-mono" value="0" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Footer Card -->
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 p-4 mb-4 bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input fs-4 cursor-pointer" type="checkbox" name="is_active" id="activeItemSwitch" checked>
                            <label class="form-check-label font-bold text-slate-800 fs-6 ms-2 cursor-pointer" for="activeItemSwitch">تفعيل الصنف للبيع والشراء</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-2.5 font-bold rounded-3 shadow-sm hover-lift">
                            <i class="bi bi-check-circle me-1.5"></i>حفظ الصنف الجديد
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
