<?php

return [
    'open_cashboxes' => 'الصناديق المفتوحة',
    'pending_cheques' => 'الشيكات المعلقة',

    // Cashboxes
    'cashboxes_title' => 'الخزن والصناديق المالية',
    'cashboxes_list' => 'قائمة الخزن والصناديق',
    'create_cashbox' => 'إضافة خزنة جديدة',
    'cashbox_code' => 'كود الخزنة',
    'cashbox_name' => 'اسم الخزنة',
    'opening_balance' => 'الرصيد الافتتاحي',
    'current_balance' => 'الرصيد الحالي',
    'authorized_users' => 'المستخدمون المسموح لهم',

    // Shifts & Reconciliations
    'shift_status' => 'حالة الوردية',
    'open_shift' => 'فتح وردية وجرد',
    'close_shift' => 'إغلاق الوردية والحساب',
    'opened_at' => 'تاريخ فتح الوردية',
    'closed_at' => 'تاريخ إغلاق الوردية',
    'expected_closing_balance' => 'الرصيد المتوقع في النظام',
    'actual_closing_balance' => 'الرصيد الفعلي بعد الجرد',
    'difference_amount' => 'الفارق (عجز / زيادة)',
    'shift_open' => 'وردية مفتوحة',
    'shift_closed' => 'مغلقة',

    // Payment Vouchers
    'vouchers_title' => 'سندات المقبوضات والمصروفات',
    'vouchers_list' => 'قائمة السندات المالية',
    'create_voucher' => 'إصدار سند مالي جديد',
    'voucher_number' => 'رقم السند',
    'voucher_type' => 'نوع السند',
    'types' => [
        'receipt' => 'سند قبض',
        'payment' => 'سند صرف',
        'transfer' => 'تحويل بين الخزن',
    ],
    'payment_date' => 'تاريخ السداد',
    'amount' => 'المبلغ الإجمالي',
    'print_receipt' => 'طباعة إيصال السداد',

    // Payment Methods
    'methods' => [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'card' => 'بطاقة / شبكة',
        'cheque' => 'شيك',
        'credit' => 'آجل',
        'e_wallet' => 'محفظة إلكترونية',
        'other' => 'طريقة أخرى',
    ],

    // Cheques
    'cheques_title' => 'إدارة الشيكات',
    'cheques_list' => 'قائمة الشيكات',
    'cheque_number' => 'رقم الشيك',
    'bank_name' => 'اسم البنك',
    'drawer_name' => 'صاحب الشيك / الساحب',
    'issue_date' => 'تاريخ الإصدار',
    'due_date' => 'تاريخ الاستحقاق',
    'cheque_statuses' => [
        'received' => 'مستلم',
        'under_collection' => 'تحت التحصيل',
        'collected' => 'تم التحصيل',
        'returned' => 'مرتجع',
        'cancelled' => 'ملغي',
        'deferred' => 'مؤجل',
    ],
];
