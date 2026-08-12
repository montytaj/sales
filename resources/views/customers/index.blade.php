<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('customers.customers_list')]
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('customers.customers_list')" :description="__('customers.manage_desc') ?? 'إدارة دليل العملاء والبيانات المالية والائتمان'">
        <x-slot name="actions">
            @can('create-customers')
                <a href="{{ route('customers.create') }}" class="btn btn-primary-custom shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    <span>{{ __('customers.create_customer') }}</span>
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <!-- Unified Filter Bar -->
    <x-filter-bar 
        :action="route('customers.index')" 
        :searchPlaceholder="__('customers.search_placeholder') ?? 'بحث بالاسم، الكود، الهاتف، أو الرقم الضريبي...'"
        :statuses="[
            '1' => __('customers.active'),
            '0' => __('customers.inactive')
        ]" />

    <!-- Customers Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">{{ __('customers.name') }}</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">{{ __('customers.type') }}</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">{{ __('customers.phone') }}</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">{{ __('customers.category') }}</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">{{ __('customers.credit_limit') }}</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">{{ __('customers.status') }}</th>
                        <th scope="col" class="text-end pe-3 text-slate-600 font-semibold fs-7">{{ __('general.actions') ?? 'الإجراءات' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-900 text-decoration-none hover-primary">
                                    {{ $customer->name }}
                                </a>
                                @if ($customer->company_name)
                                    <small class="text-muted d-block fs-8">{{ $customer->company_name }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($customer->type === 'company')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-medium fs-8">
                                        <i class="bi bi-building me-1"></i>{{ __('customers.company') }}
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 border border-slate-200 font-medium fs-8">
                                        <i class="bi bi-person me-1"></i>{{ __('customers.individual') }}
                                    </span>
                                @endif
                            </td>
                            <td class="font-mono fs-7 text-slate-700 dir-ltr text-end text-md-start">{{ $customer->phone }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle font-medium fs-8">
                                    {{ __('customers.' . $customer->category) }}
                                </span>
                            </td>
                            <td>
                                <div class="font-bold text-slate-800 fs-7">{{ number_format($customer->credit_limit, 2) }}</div>
                                <small class="text-muted fs-8">({{ $customer->credit_period_days }} يوم)</small>
                            </td>
                            <td>
                                <x-status-badge :status="$customer->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('edit-customers')
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-action-icon btn-action-edit" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete-customers')
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من رغبتك في حذف هذا العميل؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-icon btn-action-delete" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-empty-state 
                                    icon="bi-person-vcard" 
                                    :title="__('customers.no_customers_found') ?? 'لا يوجد عملاء مطابقين'" 
                                    :actionUrl="route('customers.create')" 
                                    :actionLabel="__('customers.create_customer')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="card-footer bg-white border-top border-slate-100 py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

