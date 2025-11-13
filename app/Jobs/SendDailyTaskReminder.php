<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Assignment;
use App\Models\WaGroup;
use App\Models\BroadcastLog; // Import Model Log
use App\Services\FonnteService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SendDailyTaskReminder implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function handle(): void
    {
        $now = now('Asia/Jakarta');

        // Ambil tugas yang deadline >= hari ini
        $assignments = Assignment::with('course')
            ->where('status', 'open')
            ->whereDate('deadline', '>=', $now->toDateString())
            ->orderBy('deadline')
            ->get()
            ->groupBy(fn($t) => $t->course->class_name);

        foreach ($assignments as $class => $list) {

            // Ambil SEMUA grup yang cocok dengan kelas tersebut
            $groups = WaGroup::where('class_name', $class)
                ->where('is_active', true)
                ->get();

            if ($groups->isEmpty()) continue;

            $msg  = "⏰ *Reminder Tugas Harian*\n";
            $msg .= "Kelas: *$class*\n\n";

            foreach ($list as $a) {
                $msg .= "• {$a->title} — *{$a->deadline->format('d M H:i')}*\n";
            }

            foreach ($groups as $group) {
                FonnteService::sendMessage($group->group_code, $msg);

                // --- PENERAPAN LOG AKTUAL ---
                BroadcastLog::create([
                    'type' => 'reminder',
                    'target_group' => $group->name,
                    'title' => 'Daily Task Reminder',
                    'message' => $msg,
                    'status' => 'success',
                    'triggered_by' => null, // Null = Sistem
                ]);
                // ----------------------------
            }
        }
    }
}
