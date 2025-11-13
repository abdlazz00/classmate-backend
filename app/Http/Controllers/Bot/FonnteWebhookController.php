<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Services\FonnteService;

class FonnteWebhookController extends Controller
{
    public function receive(Request $r)
    {
        $sender = $r->sender ?? null;
        if (!$sender) {
            return 'ignores';
        }
        $key = 'rate_limit_wa_' . $sender;
        if (\Cache::has($key)) {
            FonnteService::sendMessage($sender, "⛔ Mohon tunggu 10 detik sebelum mengirim perintah lagi.");
            return 'rate_limited';
        }
        \Cache::put($key, true, now()->addSecond(10));

        $msg   = strtolower($r->message ?? '');
        $group = $r->groupName ?? '';

        if (!$group) return;

        $class = $this->extractClassName($group);

        if (str_contains($msg, 'jadwal')) {
            return $this->replySchedule($class, $group);
        }

        if (str_contains($msg, 'tugas')) {
            return $this->replyTasks($class, $group);
        }

        if (str_contains($msg, 'pengumuman')) {
            return $this->replyAnnouncements($class, $group);
        }

        return 'ignored';
    }

    private function extractClassName($group)
    {
        return preg_match('/\b([1-9][A-Z])\b/i', $group, $m) ? strtoupper($m[1]) : null;
    }

    private function replySchedule($class, $group)
    {
        $hari = now('Asia/Jakarta')->format('l');
        $map = [
            'Monday'=>'senin','Tuesday'=>'selasa','Wednesday'=>'rabu',
            'Thursday'=>'kamis','Friday'=>'jumat','Saturday'=>'sabtu'
        ];

        $data = Schedule::with('course')
            ->whereHas('course',fn($q)=>$q->where('class_name',$class))
            ->where('day', $map[$hari] ?? null)
            ->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($group, "Hari ini tidak ada jadwal 👍");
        }

        $msg = "📚 *Jadwal Hari Ini ($class)*\n\n";
        foreach ($data as $d) {
            $msg .= "• {$d->course->name} ({$d->start_time}-{$d->end_time})\n";
        }

        return FonnteService::sendMessage($group, $msg);
    }

    private function replyTasks($class, $group)
    {
        $data = Assignment::with('course')
            ->whereHas('course',fn($q)=>$q->where('class_name',$class))
            ->where('status','open')
            ->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($group, "Tidak ada tugas aktif.");
        }

        $msg = "📝 *Tugas Aktif ($class)*\n\n";
        foreach ($data as $t) {
            $msg .= "• {$t->title} — *{$t->deadline->format('d M H:i')}*\n";
        }

        return FonnteService::sendMessage($group, $msg);
    }

    private function replyAnnouncements($class, $group)
    {
        $data = Announcement::where(function($q) use ($class){
            $q->whereNull('class_name')->orWhere('class_name',$class);
        })->latest()->limit(5)->get();

        if ($data->isEmpty()) {
            return FonnteService::sendMessage($group, "Belum ada pengumuman.");
        }

        $msg = "📢 *Pengumuman ($class)*\n\n";
        foreach ($data as $a) {
            $msg .= "• *{$a->title}*\n";
        }

        return FonnteService::sendMessage($group, $msg);
    }
}
