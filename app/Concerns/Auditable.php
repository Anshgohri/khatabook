<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait Auditable
{
    /**
     * @var array<int, string>
     */
    protected static array $auditableExcept = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'created_at', 'updated_at'];

    public static function bootAuditable(): void
    {
        static::created(fn (self $model) => $model->recordAuditLog('created', [], $model->getAttributes()));

        static::updated(function (self $model) {
            $changes = $model->getChanges();

            $model->recordAuditLog('updated', array_intersect_key($model->getOriginal(), $changes), $changes);
        });

        static::deleted(fn (self $model) => $model->recordAuditLog('deleted', $model->getOriginal(), []));
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    protected function recordAuditLog(string $action, array $oldValues, array $newValues): void
    {
        $oldValues = array_diff_key($oldValues, array_flip(static::$auditableExcept));
        $newValues = array_diff_key($newValues, array_flip(static::$auditableExcept));

        if ($action === 'updated' && $newValues === []) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);
    }
}
