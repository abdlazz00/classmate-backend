<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Models\WaGroup;
use App\Models\BroadcastLog; // Import Model Log
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAssignmentToWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param Assignment $assignment
     * @param array $targetGroupIds (Opsional) ID grup target
     * @param int|null $userId (Opsional) ID Admin yang mengirim
     */
    public function __construct(
        public Assignment $assignment,
        public array $targetGroupIds = [],
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        // 1. Tentukan Grup Tujuan
        if (!empty($this->targetGroupIds)) {
            // Jika Admin memilih manual
            $groups = WaGroup::whereIn('id', $this->targetGroupIds)
                ->where('is_active', true)
                ->get();
        } else {
            // Fallback otomatis berdasarkan nama kelas
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

        // 3. Loop Kirim & Log
        foreach ($groups as $group) {
            // Kirim WA
            $response = FonnteService::sendMessage($group->group_code, $msg);

            // --- PENERAPAN LOG AKTUAL ---
            BroadcastLog::create([
                'type' => 'assignment',
                'target_group' => $group->name,
                'title' => $this->assignment->title,
                'message' => $msg,
                'status' => 'success', // Asumsi sukses masuk antrian Fonnte
                'triggered_by' => $this->userId, // ID Admin
            ]);
            // ----------------------------
        }
    }
}
