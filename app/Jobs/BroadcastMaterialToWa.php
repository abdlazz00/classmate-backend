<?php

namespace App\Jobs;

use App\Models\Material;
use App\Models\WaGroup;
use App\Models\BroadcastLog; // Import Model Log
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastMaterialToWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Material $material,
        public array $targetGroupIds = [],
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        // 1. Tentukan Grup
        if (!empty($this->targetGroupIds)) {
            $groups = WaGroup::whereIn('id', $this->targetGroupIds)->where('is_active', true)->get();
        } else {
            // Fallback logika otomatis jika diperlukan
            $groups = WaGroup::where('class_name', $this->material->course->class_name ?? '')
                ->where('is_active', true)
                ->get();
        }

        if ($groups->isEmpty()) return;

        // 2. Susun Pesan & Link
        $link = asset('storage/' . $this->material->file_path);
        $caption = "*Materi Baru: {$this->material->title}* 📚\n";
        $caption .= "Mata Kuliah: " . ($this->material->course->name ?? '-') . "\n";
        if ($this->material->description) {
            $caption .= "\n" . $this->material->description . "\n";
        }
        $caption .= "\n🔗 Unduh: $link";

        // 3. Loop Kirim & Log
        foreach ($groups as $group) {
            if ($this->material->type === 'link') {
                FonnteService::sendMessage($group->group_code, $caption);
            } else {
                FonnteService::sendFile($group->group_code, $link, $caption);
            }

            // --- PENERAPAN LOG AKTUAL ---
            BroadcastLog::create([
                'type' => 'material',
                'target_group' => $group->name,
                'title' => $this->material->title,
                'message' => $caption,
                'status' => 'success',
                'triggered_by' => $this->userId,
            ]);
            // ----------------------------
        }
    }
}
