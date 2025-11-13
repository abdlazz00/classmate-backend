<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\WaGroup;
use App\Models\BroadcastLog; // Import Model Log
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAnnouncementToWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public array $targetGroupIds = [],
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        $data = Announcement::find($this->announcement->id);
        if (!$data) return;

        // 1. Tentukan Grup
        if (!empty($this->targetGroupIds)) {
            $groups = WaGroup::whereIn('id', $this->targetGroupIds)->where('is_active', true)->get();
        } else {
            if ($data->class_name) {
                $groups = WaGroup::where('class_name', $data->class_name)->where('is_active', true)->get();
            } else {
                $groups = WaGroup::where('is_active', true)->get();
            }
        }

        // 2. Susun Pesan
        $msg  = "📢 *PENGUMUMAN*\n";
        $msg .= "*{$data->title}*\n\n";
        $msg .= "{$data->message}";

        // 3. Loop Kirim & Log
        foreach ($groups as $group) {
            FonnteService::sendMessage($group->group_code, $msg);

            // --- PENERAPAN LOG AKTUAL ---
            BroadcastLog::create([
                'type' => 'announcement',
                'target_group' => $group->name,
                'title' => $data->title,
                'message' => $msg,
                'status' => 'success',
                'triggered_by' => $this->userId,
            ]);
            // ----------------------------
        }
    }
}
