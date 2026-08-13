<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index($locale = 'ar', Request $request = null)
    {
        $this->authorize('manage-settings');

        $groupedSettings = $this->settingsService->getAllGrouped();

        return view('settings.index', compact('groupedSettings'));
    }

    public function update($locale = 'ar', Request $request = null)
    {
        $request = $request ?: request();
        $this->authorize('manage-settings');

        // Normalize typed hex colors (prepend # if omitted by user)
        foreach (['primary_color', 'secondary_color', 'accent_color', 'sidebar_bg', 'sidebar_icon_color'] as $colKey) {
            if ($request->filled($colKey)) {
                $cVal = trim($request->input($colKey));
                if (!str_starts_with($cVal, '#')) {
                    $cVal = '#' . $cVal;
                }
                $request->merge([$colKey => $cVal]);
            }
        }

        $validated = $request->validate([
            // General
            'facility_name' => ['required', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'accent_color' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'sidebar_bg' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'sidebar_icon_color' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:100'],
            'default_locale' => ['required', 'string', 'in:ar,en'],
            'system_start_date' => ['nullable', 'date'],
            'sales_system_mode' => ['nullable', 'string', 'in:standard,pos'],

            // Financial
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'number_format' => ['required', 'integer', 'min:0', 'max:4'],
            'doc_prefix_quotation' => ['required', 'string', 'max:20'],
            'doc_prefix_invoice' => ['required', 'string', 'max:20'],
            'doc_prefix_work_order' => ['required', 'string', 'max:20'],
            'allow_negative_inventory' => ['boolean'],
            'allow_delivery_with_balance' => ['boolean'],
            'min_downpayment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],

            // Feature Flags
            'inventory_enabled' => ['boolean'],
            'accounting_enabled' => ['boolean'],
            'cheques_enabled' => ['boolean'],
            'projects_enabled' => ['boolean'],
            'signage_enabled' => ['boolean'],
            'quick_actions_enabled' => ['boolean'],
        ]);

        // General Settings & Branding
        $this->settingsService->set('facility_name', $validated['facility_name'], 'general', 'string');
        $this->settingsService->set('primary_color', $validated['primary_color'] ?? '#2563eb', 'general', 'string');
        $this->settingsService->set('secondary_color', $validated['secondary_color'] ?? '#0f172a', 'general', 'string');
        $this->settingsService->set('accent_color', $validated['accent_color'] ?? '#10b981', 'general', 'string');
        $this->settingsService->set('sidebar_bg', $validated['sidebar_bg'] ?? '#0f172a', 'general', 'string');
        $this->settingsService->set('sidebar_icon_color', $validated['sidebar_icon_color'] ?? '#3b82f6', 'general', 'string');
        $this->settingsService->set('currency', $validated['currency'], 'general', 'string');
        $this->settingsService->set('timezone', $validated['timezone'], 'general', 'string');
        $this->settingsService->set('default_locale', $validated['default_locale'], 'general', 'string');
        $this->settingsService->set('system_start_date', $validated['system_start_date'] ?? date('Y-m-d'), 'general', 'string');
        $this->settingsService->set('sales_system_mode', $validated['sales_system_mode'] ?? 'standard', 'general', 'string');

        // Logo Upload & Delete logic
        if ($request->boolean('remove_logo')) {
            $oldLogo = $this->settingsService->get('logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $this->settingsService->set('logo', null, 'general', 'string');
        } elseif ($request->hasFile('logo')) {
            $oldLogo = $this->settingsService->get('logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $this->settingsService->set('logo', $path, 'general', 'string');
        }

        // Financial & Document Settings
        $this->settingsService->set('tax_percentage', $validated['tax_percentage'], 'financial', 'float');
        $this->settingsService->set('number_format', $validated['number_format'], 'financial', 'integer');
        $this->settingsService->set('doc_prefix_quotation', $validated['doc_prefix_quotation'], 'financial', 'string');
        $this->settingsService->set('doc_prefix_invoice', $validated['doc_prefix_invoice'], 'financial', 'string');
        $this->settingsService->set('doc_prefix_work_order', $validated['doc_prefix_work_order'], 'financial', 'string');
        $this->settingsService->set('allow_negative_inventory', $request->boolean('allow_negative_inventory') ? '1' : '0', 'financial', 'boolean');
        $this->settingsService->set('allow_delivery_with_balance', $request->boolean('allow_delivery_with_balance') ? '1' : '0', 'financial', 'boolean');
        $this->settingsService->set('min_downpayment_percentage', $validated['min_downpayment_percentage'], 'financial', 'float');

        // Feature Flags
        $this->settingsService->set('inventory_enabled', $request->boolean('inventory_enabled') ? '1' : '0', 'feature_flags', 'boolean');
        $this->settingsService->set('accounting_enabled', $request->boolean('accounting_enabled') ? '1' : '0', 'feature_flags', 'boolean');
        $this->settingsService->set('cheques_enabled', $request->boolean('cheques_enabled') ? '1' : '0', 'feature_flags', 'boolean');
        $this->settingsService->set('projects_enabled', $request->boolean('projects_enabled') ? '1' : '0', 'feature_flags', 'boolean');
        $this->settingsService->set('signage_enabled', $request->boolean('signage_enabled') ? '1' : '0', 'feature_flags', 'boolean');
        $this->settingsService->set('quick_actions_enabled', $request->boolean('quick_actions_enabled') ? '1' : '0', 'feature_flags', 'boolean');

        ActivityLog::log(
            'settings_updated',
            null,
            'Updated global system settings and feature flags'
        );

        return back()->with('success', __('settings.settings_saved_successfully'));
    }

    /**
     * Generate & Download Full Database Backup (.sql)
     */
    public function downloadBackup()
    {
        $this->authorize('manage-settings');

        $tables = \DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_" . $dbName;

        $sql = "-- ==============================================\n";
        $sql .= "-- Workshop ERP Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $dbName . "\n";
        $sql .= "-- ==============================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->{$keyName} ?? current((array) $tableObj);
            if (!$tableName) continue;

            $createStmt = \DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createStmt)) {
                $sql .= "-- Table structure for `{$tableName}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createStmt[0]->{'Create Table'} . ";\n\n";

                $rows = \DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data dumping for `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $escapedValues = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes((string) $val) . "'";
                        }, array_values($rowArray));

                        $sql .= "INSERT INTO `{$tableName}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = "database_backup_" . date('Y_m_d_His') . ".sql";

        ActivityLog::log('database_backup_downloaded', null, "تم تحميل نسخة احتياطية كاملة من قاعدة البيانات باسم {$fileName}");

        return response($sql)
            ->header('Content-Type', 'application/sql; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }
}
