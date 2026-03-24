<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Runs database migrations over HTTPS when shell/SSH is unavailable.
 * Enabled only when DEPLOY_MIGRATION_TOKEN is set in .env (32+ chars).
 */
class DeployMigrationController extends Controller
{
    /**
     * Browsers open URLs with GET; migrations require POST. Explains that so users are not stuck on 404.
     */
    public function instructions(): JsonResponse
    {
        $configured = config('deploy.migration_token');
        if (! is_string($configured) || $configured === '' || strlen($configured) < 32) {
            abort(404);
        }

        return response()->json([
            'message' => 'This URL must be called with HTTP POST (opening it in the browser uses GET and will not run migrations).',
            'use_method' => 'POST',
            'use_url' => url('/api/v1/deploy/migrate'),
            'header' => 'X-Deploy-Token: <same value as DEPLOY_MIGRATION_TOKEN in .env>',
            'example_curl' => 'curl -X POST "'.url('/api/v1/deploy/migrate').'" -H "Accept: application/json" -H "X-Deploy-Token: YOUR_TOKEN"',
        ], 405);
    }

    public function migrate(Request $request): JsonResponse
    {
        $configured = config('deploy.migration_token');
        if (! is_string($configured) || $configured === '' || strlen($configured) < 32) {
            abort(404);
        }

        $provided = (string) $request->header('X-Deploy-Token', '');
        if ($provided === '') {
            $provided = (string) $request->input('token', '');
        }
        if ($provided === '' || ! hash_equals($configured, $provided)) {
            abort(404);
        }

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            Log::error('deploy.migrate.exception', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'hint' => 'Check storage/logs/laravel.log on the server and verify DB_* in .env.',
            ], 500);
        }

        $output = trim(Artisan::output()) ?: '';

        if ($exitCode !== 0) {
            Log::warning('deploy.migrate.non_zero_exit', ['exit_code' => $exitCode, 'output' => $output]);

            return response()->json([
                'ok' => false,
                'message' => 'Migration command finished with a non-zero exit code.',
                'exit_code' => $exitCode,
                'output' => $output !== '' ? $output : '(no console output; see laravel.log)',
                'hint' => 'Common causes: wrong DB credentials in .env, MySQL user lacks privileges, or open_basedir blocking artisan.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'output' => $output !== '' ? $output : '(no output)',
        ]);
    }
}
