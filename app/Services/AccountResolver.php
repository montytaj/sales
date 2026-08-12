<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Cashbox;
use App\Models\Customer;
use App\Models\Supplier;

class AccountResolver
{
    /**
     * Resolve Sales Revenue Account.
     */
    public static function getSalesAccount(?int $overrideId = null): ?Account
    {
        if ($overrideId) {
            $account = Account::find($overrideId);
            if ($account) return $account;
        }

        $settingId = setting('default_account_sales_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '411101')->first()
            ?? Account::where('type', 'revenue')->where('is_selectable', true)->first()
            ?? Account::where('type', 'revenue')->first()
            ?? Account::where('name', 'like', '%مبيعات%')->first();
    }

    /**
     * Resolve Purchase / Inventory Account.
     */
    public static function getPurchaseInventoryAccount(?int $overrideId = null): ?Account
    {
        if ($overrideId) {
            $account = Account::find($overrideId);
            if ($account) return $account;
        }

        $settingId = setting('default_account_inventory_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '113101')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%مخزون%')->first()
            ?? Account::where('name', 'like', '%مخزون%')->first()
            ?? Account::where('type', 'expense')->where('name', 'like', '%مشتريات%')->first()
            ?? Account::where('type', 'asset')->where('is_selectable', true)->first();
    }

    /**
     * Resolve VAT Account.
     */
    public static function getVatAccount(?int $overrideId = null): ?Account
    {
        if ($overrideId) {
            $account = Account::find($overrideId);
            if ($account) return $account;
        }

        $settingId = setting('default_account_vat_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '212101')->first()
            ?? Account::where('name', 'like', '%ضريبة%')->first()
            ?? Account::where('type', 'liability')->where('is_selectable', true)->first()
            ?? Account::where('type', 'liability')->first();
    }

    /**
     * Resolve Accounts Receivable (AR) / Customer Account.
     */
    public static function getCustomerAccount(?Customer $customer = null): ?Account
    {
        if ($customer && $customer->account_id) {
            $acc = Account::find($customer->account_id);
            if ($acc) return $acc;
        }

        $settingId = setting('default_account_ar_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '112101')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%عملاء%')->first()
            ?? Account::where('name', 'like', '%عملاء%')->first()
            ?? Account::where('type', 'asset')->where('is_selectable', true)->first();
    }

    /**
     * Resolve Accounts Payable (AP) / Supplier Account.
     */
    public static function getSupplierAccount(?Supplier $supplier = null): ?Account
    {
        if ($supplier && $supplier->account_id) {
            $acc = Account::find($supplier->account_id);
            if ($acc) return $acc;
        }

        $settingId = setting('default_account_ap_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '211101')->first()
            ?? Account::where('type', 'liability')->where('name', 'like', '%مورد%')->first()
            ?? Account::where('name', 'like', '%مورد%')->first()
            ?? Account::where('type', 'liability')->where('is_selectable', true)->first();
    }

    /**
     * Resolve Cash / Cashbox Account.
     */
    public static function getCashboxAccount(?Cashbox $cashbox = null): ?Account
    {
        if ($cashbox && $cashbox->account_id) {
            $acc = Account::find($cashbox->account_id);
            if ($acc) return $acc;
        }

        if ($cashbox) {
            $byName = Account::where('name', 'like', "%{$cashbox->name_ar}%")
                ->orWhere('name', 'like', "%{$cashbox->name_en}%")
                ->first();
            if ($byName) return $byName;
        }

        $settingId = setting('default_account_cash_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '111101')->first()
            ?? Account::where('code', '111102')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%صندوق%')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%خزينة%')->first()
            ?? Account::where('type', 'asset')->where('is_selectable', true)->first();
    }

    /**
     * Resolve Bank Account.
     */
    public static function getBankAccount(?int $overrideId = null): ?Account
    {
        if ($overrideId) {
            $account = Account::find($overrideId);
            if ($account) return $account;
        }

        $settingId = setting('default_account_bank_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '111201')->first()
            ?? Account::where('code', '111202')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%بنك%')->first()
            ?? Account::where('type', 'asset')->where('is_selectable', true)->first();
    }

    /**
     * Resolve Cheques Under Collection Account.
     */
    public static function getChequesUnderCollectionAccount(): ?Account
    {
        $settingId = setting('default_account_cheque_id');
        if ($settingId && ($acc = Account::find($settingId))) {
            return $acc;
        }

        return Account::where('code', '112201')->first()
            ?? Account::where('type', 'asset')->where('name', 'like', '%شيك%')->first()
            ?? static::getCashboxAccount();
    }
}
