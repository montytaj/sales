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
            <div class="col-md-8">
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

                <!-- Units & Multi-Unit Setup -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-rulers text-primary me-2"></i>تحديد وحدات القياس ومُعامل التحويل (الوحدة الصغرى والكبرى)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label font-bold">الوحدة الصغرى / الفرادي (Base Unit) <span class="text-danger">*</span></label>
                                <select name="base_unit_id" class="form-select" required>
                                    <option value="">-- اختر الوحدة الصغرى --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">القطعة / العلبة / الكيلو</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">الوحدة الكبرى / الجملة (Wholesale Unit)</label>
                                <select name="wholesale_unit_id" class="form-select">
                                    <option value="">-- لا يوجد (بيع فرادي فقط) --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">الكرتونة / الصندوق / الطرد</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">معامل التحويل (عدد الوحدات الصغرى) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="conversion_factor" class="form-control" value="10" required>
                                <small class="text-muted">مثال: 1 كرتونة تحتوي على 10 قطع</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prices -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-currency-dollar text-primary me-2"></i>تسعير الصنف (التكلفة، الجملة، التجزئة)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label font-bold">سعر التكلفة (للوحدة الصغرى) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">سعر بيع الجملة (للكرتونة) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="wholesale_price" class="form-control" value="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-bold">سعر بيع القطاعي/الفرادي (للقطعة) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="retail_price" class="form-control" value="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Initial Stock & Action -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-houses text-primary me-2"></i>المخزون الافتتاحي بالمخازن</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="fs-7 text-muted mb-3">أدخل كمية المخزون الافتتاحي (بالوحدة الكبرى / الكراتين) لكل مخزن:</p>
                        @foreach($warehouses as $wh)
                            <div class="mb-3">
                                <label class="form-label font-bold text-dark">{{ $wh->name }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="initial_stock[{{ $wh->id }}]" class="form-control" value="0">
                                    <span class="input-group-text bg-light text-muted fs-7">وحدة كبرى</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3 p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeItemSwitch" checked>
                        <label class="form-check-label font-bold" for="activeItemSwitch">تفعيل الصنف للبيع والشراء</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 font-bold">
                        <i class="bi bi-check-circle me-1"></i>حفظ الصنف الجديد
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
