<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Models\WaGroup;
use App\Models\BroadcastLog;
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastAssignmentToWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Assignment $assignment,
        public array $targetGroupIds = [],
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        // 1. Tentukan Grup
        if (!empty($this->targetGroupIds)) {
            $groups = WaGroup::whereIn('id', $this->targetGroupIds)->where('is_active', true)->get();
        } else {
            // Reload relation course untuk akses class_name
            $assignment = Assignment::with('course')->find($this->assignment->id);
            if (!$assignment) return;

            $groups = WaGroup::where('class_name', $assignment->course->class_name)
                ->where('is_active', true)
                ->get();
        }

        if ($groups->isEmpty()) return;

        // 2. Susun Pesan
        $msg = "📝 *TUGAS BARU*\n";
        $msg .= "Mata Kuliah: *{$this->assignment->course->name}*\n";
        $msg .= "Judul: *{$this->assignment->title}*\n";
        $msg .= "Deadline: *" . $this->assignment->deadline->format('d M Y H:i') . "*\n";
        $msg .= "\n{$this->assignment->description}";

        // 3. Loop Kirim & Log dengan Error Handling
        foreach ($groups as $group) {
            try {
                $response = FonnteService::sendMessage($group->group_code, $msg);
                $responseBody = $response->json();
                $isSuccess = $response->successful() && ($responseBody['status'] ?? false) == true;

                BroadcastLog::create([
                    'type' => 'assignment',
                    'target_group' => $group->name,
                    'title' => $this->assignment->title,
                    'message' => $msg,
                    'status' => $isSuccess ? 'success' : 'failed',
                    'note' => $isSuccess ? null : json_encode($responseBody),
                    'triggered_by' => $this->userId,
                ]);

            } catch (\Exception $e) {
                Log::error("Broadcast Tugas Gagal ke {$group->name}: " . $e->getMessage());

                BroadcastLog::create([
                    'type' => 'assignment',
                    'target_group' => $group->name,
                    'title' => $this->assignment->title,
                    'message' => $msg,
                    'status' => 'failed',
                    'note' => 'System Error: ' . $e->getMessage(),
                    'triggered_by' => $this->userId,
                ]);
            }
        }
    }
}
