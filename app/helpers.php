<?php

use App\Services\SettingsService;

if (!function_exists('setting')) {
    /**
     * Helper to get or set system settings centrally.
     *
     * @param string|array|null $key
     * @param mixed $default
     * @param int|null $branchId
     * @return mixed
     */
    function setting($key = null, $default = null, ?int $branchId = null)
    {
        $service = app(SettingsService::class);

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $type = is_bool($v) ? 'boolean' : (is_numeric($v) ? 'float' : 'string');
                $service->set((string) $k, $v, 'general', $type, $branchId);
            }
            return true;
        }

        if ($key === null) {
            return $service;
        }

        return $service->get((string) $key, $default, $branchId);
    }
}

if (!function_exists('currency')) {
    /**
     * Helper to get system base currency.
     *
     * @param int|null $branchId
     * @return string
     */
    function currency(?int $branchId = null): string
    {
        return (string) setting('currency', app()->getLocale() == 'ar' ? 'ر.س' : 'SAR', $branchId);
    }
}

if (!function_exists('feature_enabled')) {
    /**
     * Helper to check if a feature flag is enabled.
     */
    function feature_enabled(string $featureKey, ?int $branchId = null): bool
    {
        return app(SettingsService::class)->isFeatureEnabled($featureKey, $branchId);
    }
}
