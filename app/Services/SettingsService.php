<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache TTL in seconds (e.g. 24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Get a setting value by key, checking branch override first if provided.
     */
    public function get(string $key, $default = null, ?int $branchId = null)
    {
        $cacheKey = "setting_{$key}_branch_" . ($branchId ?? 'global');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default, $branchId) {
            // 1. Try branch specific override if branchId provided
            if ($branchId) {
                $branchSetting = Setting::where('key', $key)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($branchSetting !== null) {
                    return $branchSetting->typed_value;
                }
            }

            // 2. Fallback to global setting (branch_id is null)
            $globalSetting = Setting::where('key', $key)
                ->whereNull('branch_id')
                ->first();

            if ($globalSetting !== null) {
                return $globalSetting->typed_value;
            }

            return $default;
        });
    }

    /**
     * Set or update a setting value.
     */
    public function set(string $key, $value, string $group = 'general', string $type = 'string', ?int $branchId = null): Setting
    {
        // Convert array/json to string for storage if type is json
        $storedValue = $value;
        if ($type === 'boolean') {
            $storedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        } elseif ($type === 'json' || is_array($value)) {
            $storedValue = json_encode($value);
            $type = 'json';
        }

        $setting = Setting::updateOrCreate(
            [
                'key' => $key,
                'branch_id' => $branchId,
            ],
            [
                'value' => (string) $storedValue,
                'type' => $type,
                'group' => $group,
            ]
        );

        // Invalidate cache for this setting
        $cacheKey = "setting_{$key}_branch_" . ($branchId ?? 'global');
        Cache::forget($cacheKey);

        return $setting;
    }

    /**
     * Helper to check if a feature flag is enabled.
     */
    public function isFeatureEnabled(string $featureKey, ?int $branchId = null): bool
    {
        return (bool) $this->get($featureKey, true, $branchId);
    }

    /**
     * Get all settings grouped by group name.
     */
    public function getAllGrouped(?int $branchId = null): array
    {
        $query = Setting::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        $settings = $query->get();
        $grouped = [];

        foreach ($settings as $setting) {
            $grouped[$setting->group][$setting->key] = $setting->typed_value;
        }

        return $grouped;
    }

    /**
     * Seed default global system settings.
     */
    public function seedDefaults(): void
    {
        $defaults = [
            // General Settings
            'facility_name' => ['value' => 'مؤسسة أثاث وديكور وورش CNC', 'type' => 'string', 'group' => 'general'],
            'logo' => ['value' => null, 'type' => 'string', 'group' => 'general'],
            'primary_color' => ['value' => '#2563eb', 'type' => 'string', 'group' => 'general'],
            'secondary_color' => ['value' => '#0f172a', 'type' => 'string', 'group' => 'general'],
            'currency' => ['value' => 'SDG', 'type' => 'string', 'group' => 'general'],
            'timezone' => ['value' => 'Africa/Khartoum', 'type' => 'string', 'group' => 'general'],
            'default_locale' => ['value' => 'ar', 'type' => 'string', 'group' => 'general'],

            // Financial & Document Settings
            'tax_percentage' => ['value' => '15.00', 'type' => 'float', 'group' => 'financial'],
            'number_format' => ['value' => '2', 'type' => 'integer', 'group' => 'financial'],
            'doc_prefix_quotation' => ['value' => 'OFFER-', 'type' => 'string', 'group' => 'financial'],
            'doc_prefix_invoice' => ['value' => 'INV-', 'type' => 'string', 'group' => 'financial'],
            'doc_prefix_work_order' => ['value' => 'JOB-', 'type' => 'string', 'group' => 'financial'],
            'allow_negative_inventory' => ['value' => '0', 'type' => 'boolean', 'group' => 'financial'],
            'allow_delivery_with_balance' => ['value' => '0', 'type' => 'boolean', 'group' => 'financial'],
            'min_downpayment_percentage' => ['value' => '50.00', 'type' => 'float', 'group' => 'financial'],

            // Feature Flags
            'inventory_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'feature_flags'],
            'accounting_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'feature_flags'],
            'cheques_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'feature_flags'],
            'projects_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'feature_flags'],
            'signage_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'feature_flags'],
        ];

        foreach ($defaults as $key => $data) {
            if (!Setting::where('key', $key)->whereNull('branch_id')->exists()) {
                $this->set($key, $data['value'], $data['group'], $data['type']);
            }
        }
    }
}
