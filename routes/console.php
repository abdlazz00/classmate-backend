<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendDailyTaskReminder;
use App\Models\SchedulerSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

if (Schema::hasTable('scheduler_settings')) {
    try {
        $setting = SchedulerSetting::where('key', 'daily_reminder')->first();

        // --- DEBUG LOG ---
        // Cek file storage/logs/laravel.log untuk melihat hasil ini
        if (!$setting) {
            Log::error('SCHEDULER DEBUG: Setting daily_reminder TIDAK DITEMUKAN di Database.');
        } elseif (!$setting->is_active) {
            Log::warning('SCHEDULER DEBUG: Setting ditemukan tapi TIDAK AKTIF.');
        } else {
            Log::info("SCHEDULER DEBUG: Setting OK. Mode: {$setting->mode}, Value: {$setting->value}, Unit: {$setting->unit}");
        }
        // -----------------

        if ($setting && $setting->is_active) {
            $job = new SendDailyTaskReminder();

            if ($setting->mode === 'specific' && !empty($setting->time_details)) {
                foreach ($setting->time_details as $detail) {
                    if (isset($detail['time'])) {
                        Schedule::job($job)
                            ->dailyAt($detail['time'])
                            ->timezone('Asia/Jakarta');
                    }
                }
            }
            elseif ($setting->mode === 'interval') {
                $task = Schedule::job($job);
                switch ($setting->unit) {
                    case 'minutes':
                        $task->cron("*/{$setting->value} * * * *");
                        break;
                    case 'hours':
                        $task->cron("0 */{$setting->value} * * *");
                        break;
                    case 'days':
                        $task->cron("0 0 */{$setting->value} * *");
                        break;
                }
            }
        }
    } catch (\Exception $e) {
        Log::error('SCHEDULER ERROR: ' . $e->getMessage());
    }
    Schedule::call(function () {
        \Illuminate\Support\Facades\Log::info('✅ Scheduler Hidup! Pukul: ' . now()->toTimeString());
    })->everyMinute();
}
