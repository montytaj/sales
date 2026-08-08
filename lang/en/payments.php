<?php

return [
    'open_cashboxes' => 'Open Cashboxes',
    'pending_cheques' => 'Pending Cheques',

    // Cashboxes
    'cashboxes_title' => 'Cashboxes & Registers',
    'cashboxes_list' => 'Cashboxes List',
    'create_cashbox' => 'Add New Cashbox',
    'cashbox_code' => 'Cashbox Code',
    'cashbox_name' => 'Cashbox Name',
    'opening_balance' => 'Opening Balance',
    'current_balance' => 'Current Balance',
    'authorized_users' => 'Authorized Users',

    // Shifts & Reconciliations
    'shift_status' => 'Shift Status',
    'open_shift' => 'Open Shift & Audit',
    'close_shift' => 'Close Shift & Reconcile',
    'opened_at' => 'Shift Opened At',
    'closed_at' => 'Shift Closed At',
    'expected_closing_balance' => 'Expected Balance',
    'actual_closing_balance' => 'Actual Counted Balance',
    'difference_amount' => 'Variance (Shortage / Over)',
    'shift_open' => 'Shift Open',
    'shift_closed' => 'Closed',

    // Payment Vouchers
    'vouchers_title' => 'Receipt & Payment Vouchers',
    'vouchers_list' => 'Financial Vouchers List',
    'create_voucher' => 'Issue New Voucher',
    'voucher_number' => 'Voucher No.',
    'voucher_type' => 'Voucher Type',
    'types' => [
        'receipt' => 'Receipt Voucher',
        'payment' => 'Payment Voucher',
        'transfer' => 'Inter-cashbox Transfer',
    ],
    'payment_date' => 'Payment Date',
    'amount' => 'Total Amount',
    'print_receipt' => 'Print Payment Receipt',

    // Payment Methods
    'methods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card / POS',
        'cheque' => 'Cheque',
        'credit' => 'Credit / On Account',
        'e_wallet' => 'E-Wallet',
        'other' => 'Other Method',
    ],

    // Cheques
    'cheques_title' => 'Cheque Management',
    'cheques_list' => 'Cheques List',
    'cheque_number' => 'Cheque No.',
    'bank_name' => 'Bank Name',
    'drawer_name' => 'Drawer / Account Holder',
    'issue_date' => 'Issue Date',
    'due_date' => 'Due Date',
    'cheque_statuses' => [
        'received' => 'Received',
        'under_collection' => 'Under Collection',
        'collected' => 'Collected',
        'returned' => 'Returned',
        'cancelled' => 'Cancelled',
        'deferred' => 'Deferred',
    ],
];
