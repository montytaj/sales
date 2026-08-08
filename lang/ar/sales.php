<?php

return [
    'title' => 'المبيعات',
    'unpaid_invoices' => 'فواتير غير مسددة',

    // Customer Orders
    'orders_title' => 'طلبات واستفسارات العملاء',
    'orders_list' => 'قائمة طلبات العملاء',
    'create_order' => 'إضافة طلب عميل جديد',
    'order_number' => 'رقم الطلب',
    'requirements_summary' => 'ملخص طلب العميل والمواصفات',
    'order_status' => [
        'pending' => 'قيد الانتظار',
        'quoted' => 'تم إصدار عرض سعر',
        'converted' => 'تم التحويل إلى فاتورة',
        'cancelled' => 'ملغي',
    ],

    // Quotations
    'quotations_title' => 'عروض الأسعار',
    'quotations_list' => 'قائمة عروض الأسعار',
    'create_quotation' => 'إنشاء عرض سعر جديد',
    'edit_quotation' => 'تعديل عرض السعر',
    'show_quotation' => 'عرض تفاصيل عرض السعر',
    'quotation_number' => 'رقم عرض السعر',
    'issue_date' => 'تاريخ الإصدار',
    'expiry_date' => 'تاريخ الانتهاء',
    'subtotal' => 'المجموع قبل الضريبة',
    'discount' => 'الخصم',
    'tax_amount' => 'ضريبة القيمة المضافة (15%)',
    'total_amount' => 'الإجمالي النهائي',
    'approval_status' => 'حالة الاعتماد',
    'approved' => 'معتمد من الإدارة',
    'not_approved' => 'غير معتمد',
    'approve_action' => 'اعتماد عرض السعر',
    'convert_to_invoice' => 'تحويل إلى فاتورة مبيعات',
    'print_document' => 'طباعة المستند',
    'quotation_statuses' => [
        'draft' => 'مسودة',
        'sent' => 'مرسل للعميل',
        'accepted' => 'مقبول من العميل',
        'rejected' => 'مرفوض',
        'expired' => 'منتهي الصلاحية',
        'cancelled' => 'ملغي',
    ],

    // Invoices
    'invoices_title' => 'فواتير المبيعات',
    'invoices_list' => 'قائمة فواتير المبيعات',
    'show_invoice' => 'عرض الفاتورة',
    'invoice_number' => 'رقم الفاتورة',
    'due_date' => 'تاريخ الاستحقاق',
    'tax_invoice' => 'فاتورة ضريبية',
    'invoice_statuses' => [
        'draft' => 'مسودة',
        'issued' => 'صادرة',
        'partially_paid' => 'مدفوعة جزئياً',
        'paid' => 'مدفوعة بالكامل',
        'overdue' => 'متأخرة عن السداد',
        'cancelled' => 'ملغاة',
    ],

    // Items
    'items' => 'بنود المستند والخدمات',
    'add_item' => 'إضافة بند جديد',
    'item_name' => 'اسم البند / الخدمة',
    'quantity' => 'الكمية',
    'unit' => 'الوحدة',
    'unit_price' => 'سعر الوحدة',
    'item_discount' => 'خصم البند',
    'item_total' => 'الإجمالي',
    'terms_and_conditions' => 'الشروط والأحكام',
];
