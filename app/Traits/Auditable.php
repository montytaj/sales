<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::recordActivity($model, 'created', 'تم إنشاء سجل جديد');
        });

        static::updated(function (Model $model) {
            $changes = [];
            foreach ($model->getDirty() as $key => $newValue) {
                // Ignore timestamp fields
                if (in_array($key, ['updated_at', 'created_at', 'remember_token'])) continue;

                $oldValue = $model->getOriginal($key);
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }

            if (!empty($changes)) {
                self::recordActivity($model, 'updated', 'تم تعديل السجل', ['changes' => $changes]);
            }
        });

        static::deleted(function (Model $model) {
            self::recordActivity($model, 'deleted', 'تم حذف السجل');
        });
    }

    protected static function recordActivity(Model $model, string $action, string $defaultDesc, array $extraProps = []): void
    {
        try {
            $modelName = class_basename($model);
            $desc = "{$defaultDesc} في ({$modelName}) برقم #{$model->getKey()}";

            $properties = array_merge([
                'attributes' => $model->getAttributes(),
            ], $extraProps);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => strtolower($modelName) . '_' . $action,
                'subject_type' => get_class($model),
                'subject_id' => $model->getKey(),
                'description' => $desc,
                'properties' => $properties,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Fail safely without blocking core business transactions
        }
    }
}
