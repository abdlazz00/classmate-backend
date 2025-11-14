<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Assignment;
use App\Models\WaGroup;
use App\Models\LogReminder; // Pastikan pakai LogReminder
use App\Services\FonnteService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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

            // Susun Pesan Digest (Satu pesan berisi banyak tugas)
            $msg  = "⏰ *Reminder Tugas Harian*\n";
            $msg .= "Kelas: *$class*\n\n";
            foreach ($list as $a) {
                $msg .= "• {$a->title} — *{$a->deadline->format('d M H:i')}*\n";
            }

            // Kirim ke setiap grup yang relevan
            foreach ($groups as $group) {
                $status = 'success';
                $note = null;

                try {
                    $response = FonnteService::sendMessage($group->group_code, $msg);
                    $responseBody = $response->json();
                    $isSuccess = $response->successful() && ($responseBody['status'] ?? false) == true;

                    $status = $isSuccess ? 'success' : 'failed';
                    $note = $isSuccess ? null : json_encode($responseBody);

                } catch (\Exception $e) {
                    $status = 'failed';
                    $note = 'System Error: ' . $e->getMessage();
                    Log::error("Gagal kirim reminder harian ke {$group->name}: " . $e->getMessage());
                }

                // REKAM LOG KE LogReminder (Satu entri per tugas yang di-reminder)
                // Karena tabel LogReminder butuh 'assignment_id', kita loop list tugasnya
                foreach ($list as $assignmentItem) {
                    LogReminder::create([
                        'assignment_id' => $assignmentItem->id,
                        'group_name' => $group->name,
                        'message' => $msg, // Pesan yang sama untuk semua item
                        'sent_at' => now(),
                        'status' => $status,
                        'note' => $note, // Simpan detail error jika ada
                    ]);
                }
            }
        }
    }
}
