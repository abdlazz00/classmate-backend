<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Assignment;
use App\Models\WaGroup;
use App\Services\FonnteService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
class SendDailyTaskReminder implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = now('Asia/Jakarta');
        $assignments = Assignment::with('course')
            ->where('status', 'open')
            ->whereDate('deadline', '>=', $now->toDateString())
            ->orderBy('deadline')
            ->get()
            ->groupBy(fn($t) => $t->course->class_name);

        foreach ($assignments as $class => $list) {

            $group = WaGroup::where('class_name',$class)->where('is_active',true)->first();
            if (!$group) continue;

            $msg  = "⏰ *Reminder Tugas*\n";
            $msg .= "Kelas: *$class*\n\n";

            foreach ($list as $a) {
                $msg .= "• {$a->title} — *{$a->deadline->format('d M H:i')}*\n";
            }

            FonnteService::sendMessage($group->group_code, $msg);


        }
    }
}
