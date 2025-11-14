<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Announcement;
use App\Models\User;
use App\Models\WaGroup;
use Illuminate\Http\Request;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FonnteWebhookController extends Controller
{
    public function receive(Request $r)
    {
        Log::info('📩 Webhook Masuk:', $r->all());

        $sender = $r->sender ?? null;
        $message = $r->message ?? '';
        $isGroup = isset($r->isgroup) && ($r->isgroup === true || $r->isgroup === 'true');

        if (!$sender) return 'ignores';

        $msg = strtolower($message);
        $class = null;
        $target = $sender;

        // ---------------------------------------------------------
        // 1. LOGIKA PENENTUAN KELAS
        // ---------------------------------------------------------

        if ($isGroup) {
            // Cek Grup di DB
            $groupData = WaGroup::where('group_code', $sender)->first();
            if ($groupData) {
                $class = $groupData->class_name;
            } else {
                // Jika grup belum terdaftar, kita tetap izinkan !helper agar user tau ID Grupnya
                if (str_contains($msg, '!helper') || str_contains($msg, '!help')) {
                    return FonnteService::sendMessage($target, "⚠️ Grup ini belum terdaftar di sistem.\nID Grup: $sender\nSilakan hubungi Admin untuk mendaftarkan grup ini.");
                }
            }

        } else {
            // Cek User di DB
            $user = User::where('phone', $sender)
                ->orWhere('phone', 'like', '%' . substr($sender, 3))
                ->first();
            if ($user) {
                $class = $user->class_name;
            } else {
                if (str_contains($msg, '!helper') || str_contains($msg, '!help')) {
                    return FonnteService::sendMessage($target, "⚠️ Nomor Anda belum terdaftar di database mahasiswa.");
                }
            }
        }

        // Jika kelas tidak ketemu & bukan minta helper, hentikan.
        if (!$class) {
            return 'unknown_class';
        }

        // ---------------------------------------------------------
        // 2. LOGIKA PERINTAH (COMMANDS)
        // ---------------------------------------------------------

        // Command: !helper atau !help
        if (str_contains($msg, '!helper') || str_contains($msg, '!help') || str_contains($msg, 'bantuan')) {
            return $this->replyHelper($target, $class);
        }

        // Command: !jadwal
        if (str_contains($msg, '!jadwal') || str_contains($msg, 'jadwal hari ini')) {
            return $this->replySchedule($class, $target);
        }

        // Command: !tugas
        if (str_contains($msg, '!tugas')) {
            return $this->replyTasks($class, $target);
        }

        // Command: !pengumuman
        if (str_contains($msg, '!pengumuman')) {
            return $this->replyAnnouncements($class, $target);
        }

        return 'ignored';
    }

    // ---------------------------------------------------------
    // FUNGSI BALASAN (REPLY)
    // ---------------------------------------------------------

    private function replyHelper($target, $class)
    {
        $msg = "🤖 *Menu Bantuan Classmate Bot*\n";
        $msg .= "Kelas: *$class*\n\n";

//        $msg .= "Berikut adalah perintah yang bisa digunakan:\n\n";

        $msg .= "*!jadwal*\n";
        $msg .= "Melihat jadwal kuliah untuk hari ini.\n\n";

        $msg .= "*!tugas*\n";
        $msg .= "Melihat daftar tugas yang sedang aktif.\n\n";

        $msg .= "*!pengumuman*\n";
        $msg .= "Melihat pengumuman terbaru.\n\n";

        $msg .= "ℹ*!helper*\n";
        $msg .= "Menampilkan menu bantuan ini.\n\n";

        $msg .= "_Tips: Gunakan perintah ini di dalam Grup Kelas atau Chat Pribadi ke Bot._";

        return FonnteService::sendMessage($target, $msg);
    }

    private function replySchedule($class, $target)
    {
        $hari = now('Asia/Jakarta')->format('l');
        $map = [
            'Monday'=>'senin','Tuesday'=>'selasa','Wednesday'=>'rabu',
            'Thursday'=>'kamis','Friday'=>'jumat','Saturday'=>'sabtu','Sunday'=>'minggu'
        ];

        $hariIndo = $map[$hari] ?? 'minggu';

        $data = Schedule::with('course')
            ->whereHas('course', fn($q) => $q->where('class_name', $class))
            ->where('day', $hariIndo)
            ->orderBy('start_time')
            ->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($target, "📚 *Jadwal ($class)*\nTidak ada Perkuliahan hari ini. Istirahatlah! 👍");
        }

        $msg = "📚 *Jadwal Kuliah Hari Ini ($class)*\n";
        $msg .= "🗓️ {$hariIndo}, " . now('Asia/Jakarta')->format('d M Y') . "\n\n";

        foreach ($data as $d) {
            $jam = substr($d->start_time, 0, 5) . ' - ' . substr($d->end_time, 0, 5);
            $msg .= "• *{$d->course->name}*\n";
            $msg .= "   🕒 {$jam}\n";
            $msg .= "   🏢 Ruang: {$d->room}\n\n";
        }

        return FonnteService::sendMessage($target, $msg);
    }

    private function replyTasks($class, $target)
    {
        $data = Assignment::with('course')
            ->whereHas('course', fn($q) => $q->where('class_name', $class))
            ->where('status', 'open')
            ->orderBy('deadline')
            ->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($target, "📝 *Tugas ($class)*\nTidak ada tugas aktif saat ini. Aman! 😎");
        }

        $msg = "📝 *Daftar Tugas Aktif ($class)*\n\n";
        foreach ($data as $t) {
            $deadline = $t->deadline->format('d M Y, H:i');
            $msg .= "• *{$t->title}* ({$t->course->name})\n";
            $msg .= "   ⏳ Deadline: {$deadline}\n\n";
        }

        return FonnteService::sendMessage($target, $msg);
    }

    private function replyAnnouncements($class, $target)
    {
        $data = Announcement::where(function($q) use ($class){
            $q->whereNull('class_name')->orWhere('class_name', $class);
        })->latest()->limit(5)->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($target, "📢 *Pengumuman ($class)*\nBelum ada pengumuman terbaru.");
        }

        $msg = "📢 *Pengumuman Terbaru ($class)*\n\n";
        foreach ($data as $a) {
            $msg .= "• *{$a->title}*\n";
            $msg .= "   {$a->message}\n\n";
        }

        return FonnteService::sendMessage($target, $msg);
    }
}
