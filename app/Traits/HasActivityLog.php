<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Adds activity logging capabilities to a model.
 *
 * Usage: use HasActivityLog in any model that should be trackable.
 */
trait HasActivityLog
{
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    /**
     * Log an activity against this model.
     */
    public function logActivity(string $action, string $module, ?string $description = null, array $metadata = []): ActivityLog
    {
        return ActivityLog::log(
            action: $action,
            module: $module,
            description: $description,
            loggable: $this,
            metadata: $metadata,
        );
    }
}
