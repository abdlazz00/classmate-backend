<?php

namespace App\Jobs;

use App\Models\Material;
use App\Models\WaGroup;
use App\Models\BroadcastLog;
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // Tambahkan Log Facade bawaan Laravel untuk debugging sistem

class BroadcastMaterialToWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Set timeout agar job tidak menggantung selamanya jika Fonnte down
    public $timeout = 120;

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
            $groups = WaGroup::where('class_name', $this->material->course->class_name ?? '')
                ->where('is_active', true)
                ->get();
        }

        if ($groups->isEmpty()) {
            Log::warning("BroadcastMaterialToWa: Tidak ada grup target yang ditemukan untuk Materi ID: {$this->material->id}");
            return;
        }

        // 2. Susun Pesan & Link
        // PENTING: Jika di localhost, Fonnte tidak bisa membaca link 'localhost'.
        // Pastikan APP_URL di .env adalah domain publik atau IP publik (atau gunakan ngrok).
        $link = asset('storage/' . $this->material->file_path);

        $caption = "*Materi Baru: {$this->material->title}* 📚\n";
        $caption .= "Mata Kuliah: " . ($this->material->course->name ?? '-') . "\n";
        if ($this->material->description) {
            $caption .= "\n" . $this->material->description . "\n";
        }
        $caption .= "\n🔗 Unduh: $link";

        // 3. Loop Kirim & Log dengan Try-Catch
        foreach ($groups as $group) {
            try {
                $response = null;

                if ($this->material->type === 'link') {
                    $response = FonnteService::sendMessage($group->group_code, $caption);
                } else {
                    $response = FonnteService::sendFile($group->group_code, $link, $caption);
                }

                // Cek response dari Fonnte (Fonnte mengembalikan JSON)
                $responseBody = $response->json();
                $isSuccess = $response->successful() && ($responseBody['status'] ?? false) == true;

                // Simpan Log
                BroadcastLog::create([
                    'type' => 'material',
                    'target_group' => $group->name,
                    'title' => $this->material->title,
                    'message' => $caption,
                    // Dinamis berdasarkan respons API
                    'status' => $isSuccess ? 'success' : 'failed',
                    // Simpan alasan error jika ada
                    'note' => $isSuccess ? null : json_encode($responseBody),
                    'triggered_by' => $this->userId,
                ]);

            } catch (\Exception $e) {
                // Tangkap error sistem (misal koneksi internet mati)
                Log::error("Gagal mengirim WA ke grup {$group->name}: " . $e->getMessage());

                BroadcastLog::create([
                    'type' => 'material',
                    'target_group' => $group->name,
                    'title' => $this->material->title,
                    'message' => $caption,
                    'status' => 'failed',
                    'note' => 'System Error: ' . $e->getMessage(),
                    'triggered_by' => $this->userId,
                ]);
            }
        }
    }
}
