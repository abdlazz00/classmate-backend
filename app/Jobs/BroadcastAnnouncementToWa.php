<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\WaGroup;
use App\Models\BroadcastLog;
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
            try {
                $response = FonnteService::sendMessage($group->group_code, $msg);
                $responseBody = $response->json();
                $isSuccess = $response->successful() && ($responseBody['status'] ?? false) == true;

                BroadcastLog::create([
                    'type' => 'announcement',
                    'target_group' => $group->name,
                    'title' => $data->title,
                    'message' => $msg,
                    'status' => $isSuccess ? 'success' : 'failed',
                    'note' => $isSuccess ? null : json_encode($responseBody),
                    'triggered_by' => $this->userId,
                ]);

            } catch (\Exception $e) {
                Log::error("Broadcast Pengumuman Gagal ke {$group->name}: " . $e->getMessage());

                BroadcastLog::create([
                    'type' => 'announcement',
                    'target_group' => $group->name,
                    'title' => $data->title,
                    'message' => $msg,
                    'status' => 'failed',
                    'note' => 'System Error: ' . $e->getMessage(),
                    'triggered_by' => $this->userId,
                ]);
            }
        }
    }
}
