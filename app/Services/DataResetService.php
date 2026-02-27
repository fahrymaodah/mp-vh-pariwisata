<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataResetService
{
    /**
     * Module-to-table mapping for selective resets.
     */
    protected array $moduleTables = [
        'fo' => [
            'reservation_logs',
            'reservation_fix_cost_articles',
            'posting_details',
            'postings',
            'bills',
            'check_ins',
            'reservations',
        ],
        'hk' => [
            'room_maintenance_logs',
            'room_maintenance_items',
            'room_maintenances',
            'housekeeping_task_logs',
            'housekeeping_tasks',
        ],
        'sales' => [
            'guest_memberships',
            'guest_segments',
            'guest_contacts',
        ],
        'telop' => [
            'call_logs',
        ],
        'learning' => [
            'quiz_attempts',
            'quiz_questions',
            'quizzes',
            'tutorial_progress',
            'tutorials',
            'scenario_assignments',
            'scenarios',
            'activity_logs',
        ],
    ];

    /**
     * Module-to-seeder mapping.
     */
    protected array $moduleSeeders = [
        'fo' => [
            'Database\\Seeders\\ReservationSeeder',
        ],
        'hk' => [],
        'sales' => [
            'Database\\Seeders\\GuestSeeder',
        ],
        'telop' => [],
        'learning' => [
            'Database\\Seeders\\LearningSeeder',
        ],
    ];

    /**
     * Reset data for specific module.
     */
    public function resetModule(string $module): array
    {
        $tables = $this->moduleTables[$module] ?? [];
        $truncated = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $truncated[] = $table;
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Re-seed if seeders available
        $seeded = [];
        foreach ($this->moduleSeeders[$module] ?? [] as $seeder) {
            if (class_exists($seeder)) {
                Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
                $seeded[] = class_basename($seeder);
            }
        }

        return [
            'module' => $module,
            'tables_truncated' => $truncated,
            'seeders_run' => $seeded,
        ];
    }

    /**
     * Full data reset — truncates transaction tables and re-seeds.
     */
    public function resetAll(): array
    {
        $results = [];

        foreach (array_keys($this->moduleTables) as $module) {
            $results[$module] = $this->resetModule($module);
        }

        return $results;
    }

    /**
     * Get available modules for reset.
     */
    public function getModules(): array
    {
        return [
            'fo' => 'Front Office',
            'hk' => 'Housekeeping',
            'sales' => 'Sales & Marketing',
            'telop' => 'Telephone Operator',
            'learning' => 'Learning Data',
        ];
    }

    /**
     * Get table count for a module.
     */
    public function getModuleRecordCounts(string $module): array
    {
        $tables = $this->moduleTables[$module] ?? [];
        $counts = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = DB::table($table)->count();
            }
        }

        return $counts;
    }
}
