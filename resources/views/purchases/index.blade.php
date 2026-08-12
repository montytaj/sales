<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-cart-plus-fill text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إدارة فواتير المشتريات' : 'Purchase Invoices' }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('purchases.payables') }}" class="btn btn-outline-danger">
                    <i class="bi bi-hourglass-split me-1"></i>سداد الآجل ومستحقات الموردين
                </a>
                <a href="{{ route('purchases.create_invoice') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>تسجيل فاتورة شراء جديدة
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الفاتورة</th>
                            <th>المورد</th>
                            <th>المخزن</th>
                            <th>التاريخ</th>
                            <th>الدفع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseInvoices as $inv)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary font-mono">{{ $inv->invoice_number }}</span></td>
                                <td class="fw-bold text-dark">{{ $inv->supplier?->name ?? '-' }}</td>
                                <td><span class="badge bg-info-subtle text-info"><i class="bi bi-house me-1"></i>{{ $inv->warehouse?->name ?? 'المخزن الرئيسي' }}</span></td>
                                <td>{{ $inv->invoice_date }}</td>
                                <td>
                                    @if($inv->payment_type == 'cash')
                                        <span class="badge bg-success-subtle text-success">نقدي</span>
                                    @elseif($inv->payment_type == 'bank')
                                        <span class="badge bg-primary-subtle text-primary">بنكي</span>
                                    @elseif($inv->payment_type == 'split')
                                        <span class="badge bg-info-subtle text-info">دفع متعدد (كاش+بنك+آجل)</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">آجل</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark font-mono">{{ number_format($inv->net_amount, 2) }} ر.س</td>
                                <td>
                                    @if($inv->status == 'paid')
                                        <span class="badge bg-success">مدفوعة بالكامل</span>
                                    @elseif($inv->status == 'partially_paid')
                                        <span class="badge bg-warning text-dark">مدفوعة جزئياً (المتبقي: {{ number_format($inv->due_amount, 2) }})</span>
                                    @elseif($inv->status == 'unpaid')
                                        <span class="badge bg-danger">غير مدفوعة (آجل: {{ number_format($inv->due_amount, 2) }})</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $inv->status }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('purchases.show_invoice', $inv) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($inv->due_amount > 0 || $inv->status !== 'paid')
                                            <a href="{{ route('purchases.pay_invoice', $inv) }}" class="btn btn-sm btn-success fw-bold px-2 py-1" title="سداد المستحق">
                                                <i class="bi bi-wallet2 me-1"></i>سداد
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">لا توجد فواتير مشتريات مسجلة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $purchaseInvoices->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
