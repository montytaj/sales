<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\UserNotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Dispatch notification to user with deduplication check and multi-channel driver expansion.
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $priority = 'normal',
        ?string $deduplicationKey = null
    ): ?SystemNotification {
        if ($deduplicationKey) {
            $exists = SystemNotification::where('user_id', $user->id)
                ->where('deduplication_key', $deduplicationKey)
                ->exists();

            if ($exists) {
                return null; // Skip duplicate notification
            }
        }

        $notification = SystemNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'priority' => $priority,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'is_read' => false,
            'deduplication_key' => $deduplicationKey,
        ]);

        // Channel preferences
        $pref = UserNotificationPreference::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->first();

        if ($pref?->mail_enabled) {
            $this->sendMailChannel($user, $title, $message);
        }

        if ($pref?->whatsapp_enabled) {
            $this->sendWhatsAppChannel($user, $title, $message);
        }

        if ($pref?->sms_enabled) {
            $this->sendSmsChannel($user, $title, $message);
        }

        return $notification;
    }

    /**
     * Scan system triggers and create alerts for low stock, due credit sales, due cheques, and supplier payments.
     */
    public function generateSystemAlerts(User $user): void
    {
        $today = now()->format('Y-m-d');

        // 1. Low Stock Alerts
        try {
            $items = \App\Models\InventoryItem::with('warehouseItems', 'baseUnit')->get();
            foreach ($items as $item) {
                $totalStock = $item->total_stock_in_base_units;
                $minAlert = (float) ($item->min_stock_alert ?? 0);

                if ($minAlert > 0 && $totalStock <= $minAlert) {
                    $key = "low_stock_{$item->id}_{$today}";
                    $unitName = $item->baseUnit?->name ?? 'وحدة';
                    $this->send(
                        $user,
                        'inventory',
                        'تنبيه مخزون: صنف وصل لحد إعادة الطلب',
                        "الصنف ({$item->name}) الرصيد الحالي ({$totalStock} {$unitName}) أقل من أو يساوي حد الأمان ({$minAlert} {$unitName}).",
                        url('/ar/inventory'),
                        'high',
                        $key
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('Low stock notification check error: ' . $e->getMessage());
        }

        // 2. Overdue Sales Invoices / Credit Payments Alerts
        try {
            $overdueInvoices = \App\Models\Invoice::with('customer')
                ->where('status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', now())
                ->get();

            foreach ($overdueInvoices as $inv) {
                $key = "invoice_due_{$inv->id}_{$today}";
                $customerName = $inv->customer?->name ?? 'عميل آجل';
                $amountFormatted = number_format($inv->total_amount, 2);
                $currency = setting('currency', 'SAR');
                $this->send(
                    $user,
                    'financial',
                    'تنبيه سداد آجل مستحق (مبيعات)',
                    "فاتورة رقم {$inv->invoice_number} للعميل ({$customerName}) مستحقة بقيمة {$amountFormatted} {$currency}.",
                    url('/ar/reports/sales'),
                    'high',
                    $key
                );
            }
        } catch (\Throwable $e) {
            Log::error('Invoice due notification check error: ' . $e->getMessage());
        }

        // 3. Pending Cheques Due Alerts
        try {
            $pendingCheques = \App\Models\Cheque::where('status', 'pending')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', now()->addDays(2))
                ->get();

            foreach ($pendingCheques as $cheque) {
                $key = "cheque_due_{$cheque->id}_{$today}";
                $amountFormatted = number_format($cheque->amount, 2);
                $currency = setting('currency', 'SAR');
                $dueDate = $cheque->due_date?->format('Y-m-d') ?? '';
                $this->send(
                    $user,
                    'financial',
                    'تنبيه استحقاق شيك',
                    "شيك رقم ({$cheque->cheque_number}) بمبلغ {$amountFormatted} {$currency} مستحق بتاريخ {$dueDate}.",
                    url('/ar/cheques'),
                    'high',
                    $key
                );
            }
        } catch (\Throwable $e) {
            Log::error('Cheque notification check error: ' . $e->getMessage());
        }

        // 4. Overdue Supplier Purchase Invoices
        try {
            $purchaseInvoices = \App\Models\PurchaseInvoice::with('supplier')
                ->where('status', '!=', 'paid')
                ->where('payment_type', 'credit')
                ->get();

            foreach ($purchaseInvoices as $pinv) {
                $key = "purchase_due_{$pinv->id}_{$today}";
                $supplierName = $pinv->supplier?->name ?? 'مورّد';
                $amountFormatted = number_format($pinv->net_amount ?? $pinv->total_amount, 2);
                $currency = setting('currency', 'SAR');
                $this->send(
                    $user,
                    'financial',
                    'تنبيه مستحقات موردين',
                    "فاتورة مشتريات رقم {$pinv->invoice_number} للمورد ({$supplierName}) آرتفعت مستحقاتها بمبلغ {$amountFormatted} {$currency}.",
                    url('/ar/purchases'),
                    'normal',
                    $key
                );
            }
        } catch (\Throwable $e) {
            Log::error('Purchase notification check error: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Extensible Channel Driver Stubs (Future expansion for Mail, WhatsApp, SMS)
     */
    protected function sendMailChannel(User $user, string $title, string $message): void
    {
        Log::info("Mail Notification sent to {$user->email}: {$title} - {$message}");
    }

    protected function sendWhatsAppChannel(User $user, string $title, string $message): void
    {
        Log::info("WhatsApp Notification sent to {$user->phone}: {$title} - {$message}");
    }

    protected function sendSmsChannel(User $user, string $title, string $message): void
    {
        Log::info("SMS Notification sent to {$user->phone}: {$title} - {$message}");
    }
}

