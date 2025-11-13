<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\WaGroup;
use App\Services\FonnteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAnnouncementToWa implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;
    public $assignment;

    /**
     * Create a new job instance.
     */
    public function __construct(Announcement $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = Announcement::find($this->announcement->id);

        if (!$data) return;
        if ($data->class_name) {
            $groups = WaGroup::where('class_name', $data->class_name)
                ->where('is_active', true)
                ->get();
        }
        else {
            $groups = WaGroup::where('is_active', true)->get();
        }

        foreach ($groups as $group) {
            $msg  = "📢 *PENGUMUMAN*\n";
            $msg .= "*{$data->title}*\n\n";
            $msg .= "{$data->message}";

            FonnteService::sendMessage($group->group_code, $msg);
        }
    }
}
