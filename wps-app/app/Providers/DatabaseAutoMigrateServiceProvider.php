<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Applies pending migration files on HTTP bootstrap so you do not need to run
 * `php artisan migrate` on the server. Uses MySQL/MariaDB GET_LOCK when available
 * so concurrent requests do not migrate twice.
 */
class DatabaseAutoMigrateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! config('app.automatic_db_migrate')) {
            return;
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $this->runPendingMigrationsWithLock();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function runPendingMigrationsWithLock(): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $lockName = 'wps_auto_migrate';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $row = $connection->selectOne('SELECT GET_LOCK(?, 30) AS `l`', [$lockName]);
            if ((int) ($row->l ?? 0) !== 1) {
                return;
            }
            try {
                Artisan::call('migrate', ['--force' => true]);
            } finally {
                $connection->select('SELECT RELEASE_LOCK(?)', [$lockName]);
            }

            return;
        }

        Artisan::call('migrate', ['--force' => true]);
    }
}
