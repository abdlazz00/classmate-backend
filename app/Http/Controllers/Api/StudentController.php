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
        $kelas = auth()->user()->class_name;
        return [
            'nama' => auth()->user()->name,
            'nim' => auth()->user()->nim,
            'kelas' => $kelas,
            'jumlah_tugas' => Assignment::whereHas('course', fn($q) =>
            $q->where('class_name', $kelas)
            )->where('status', 'open')->count(),

            'materi_terbaru' => Material::with('course')
                ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
                ->latest()
                ->limit(3)
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
        $kelas = auth()->user()->class_name; // Tambahkan definisi variabel $kelas

        return Material::with('course')
            ->whereHas('course', fn($q) => $q->where('class_name', $kelas))
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'description' => $m->description,
                    'course' => $m->course->name,
                    'files' => $m->getMedia('files')->map(fn($f) => [
                        'name' => $f->file_name,
                        'size' => $f->human_readable_size,
                        // UPDATE URL: Arahkan ke endpoint download khusus agar tercatat log-nya
                        'url' => route('api.student.material.download', $f->id),
                        // 'original_url' => $f->getUrl() // Opsional jika butuh link asli
                    ])
                ];
            });
    }

    public function announcements()
    {
        $kelas = auth()->user()->class_name;

        return Announcement::where(function ($q) use ($kelas) {
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
            'download_at' => now(),
        ]);

        return response()->download($media->getPath(), $media->file_name);
    }
}
