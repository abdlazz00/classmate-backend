<?php

namespace Database\Seeders;

use App\Models\SchedulerSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchedulerSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchedulerSetting::updateOrCreate(
            ['key' => 'daily_reminder'],
            [
                'name' => 'Reminder Tugas Harian',
                'mode' => 'specific',
                'time_details' => ['09:00'],
                'is_active' => true,
            ]
        );
    }
}
