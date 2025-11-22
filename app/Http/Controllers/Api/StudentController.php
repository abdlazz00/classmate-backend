<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Material;
use App\Models\Announcement;
use App\Models\LogDownload; // Import Model Log
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media; // Import Media

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $kelas = $user->class_name;

        // 1. Cek Hari ini (Senin, Selasa, dst) untuk filter jadwal
        // Kita pakai array mapping manual agar aman dari settingan bahasa server
        $days = [
            1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis',
            5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'
        ];
        $hari_ini = $days[date('N')]; // date('N') return 1 (Senin) s.d 7 (Minggu)

        return [
            'nama' => $user->name,
            'nim' => $user->nim,
            'kelas' => $kelas,

            // Hitung tugas yang statusnya 'open'
            'jumlah_tugas' => Assignment::whereHas('course', fn($q) =>
            $q->where('class_name', $kelas)
            )->where('status', 'open')->count(),

            // Ambil 3 Materi Terbaru
            'materi_terbaru' => Material::with('course')
                ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
                ->latest()
                ->limit(3)
                ->get(),

            'pengumuman_terbaru' => Announcement::where(function ($q) use ($kelas) {
                $q->whereNull('class_name')->orWhere('class_name', $kelas);
            })->latest()->limit(3)->get(),

            'jadwal_hari_ini' => Schedule::with('course')
                ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
                ->where('day', $hari_ini)
                ->orderBy('start_time')
                ->get(),
        ];
    }

    public function schedules()
    {
        $kelas = auth()->user()->class_name;

        return Schedule::with('course')
            // PERBAIKAN TYPO: $$q menjadi $q
            ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
            ->orderByRaw("FIELD(day,'senin','selasa','rabu','kamis','jumat','sabtu')")
            ->orderBy('start_time')
            ->get();
    }

    public function assignments()
    {
        $kelas = auth()->user()->class_name;

        return Assignment::with('course')
            ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
            ->orderBy('deadline')
            ->get();
    }

    public function materials()
    {
        $kelas = auth()->user()->class_name;

        // Tambahkan 'uploader' di with() untuk mengambil nama dosen/admin
        return Material::with(['course', 'uploader'])
            ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
            ->latest() // Urutkan dari yang terbaru
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'description' => $m->description,
                    'course' => $m->course->name,

                    // === DATA BARU ===
                    'created_at' => $m->created_at,
                    'uploader' => $m->uploader ? $m->uploader->name : 'Admin',
                    // =================

                    'files' => $m->getMedia('files')->map(fn($f) => [
                        'name' => $f->file_name,
                        'size' => $f->human_readable_size,
                        'url' => route('api.student.material.download', $f->id),
                    ])
                ];
            });
    }

    public function announcements()
    {
        $kelas = auth()->user()->class_name;

        return Announcement::with('creator')
        ->where(function ($q) use ($kelas) {
            $q->whereNull('class_name')->orWhere('class_name', $kelas);
        })->latest()->get();

    }

    // --- FITUR BARU: DOWNLOAD LOGGING ---
    public function download($mediaId)
    {
        $media = Media::findOrFail($mediaId);

        // Cek apakah file ini milik Material
        if ($media->model_type !== 'App\Models\Material') {
            abort(404);
        }

        // Catat Log
        LogDownload::create([
            'user_id' => auth()->id(),
            'material_id' => $media->model_id,
            'downloaded_at' => now(),
        ]);

        return response()->download($media->getPath(), $media->file_name);
    }
}
