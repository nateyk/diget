<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CronJobController extends Controller
{
    public function run(Request $request)
    {
        $cronJobSettings = settings('cronjob');
        $configuredKey = (string) ($cronJobSettings->key ?? '');
        $providedKey = (string) ($request->header('X-Cron-Key') ?: $request->query('key', ''));

        if ($configuredKey === '' || $providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json(['status' => 'error', 'message' => translate('Unauthorized')], 403);
        }

        $lock = Cache::lock('cronjob:schedule-run', 120);
        if (!$lock->get()) {
            return response()->json(['status' => 'error', 'message' => translate('A scheduled task is already running.')], 409);
        }

        try {
            Artisan::call('schedule:run');
        } finally {
            $lock->release();
        }

        Settings::updateSettings('cronjob', ['last_execution' => Carbon::now()]);

        return response()->json([
            'status' => 'success',
            'message' => translate('Cron Job executed successfully'),
        ], 200);
    }
}
