<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\Course; // Import Model Course
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportSchedules implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari Course ID berdasarkan Kode MK dan Nama Kelas dari Excel
        $course = Course::where('code', $row['course_code'])
            ->where('class_name', $row['class_name'])
            ->first();

        // Jika Course tidak ditemukan, skip baris ini (atau bisa throw error)
        if (!$course) {
            return null;
        }

        // 2. Buat Jadwal
        return new Schedule([
            'course_id'  => $course->id, // Pakai ID dari hasil pencarian
            'day'        => strtolower($row['day']), // Pastikan huruf kecil (senin, selasa...)
            'start_time' => $row['start_time'], // Format Excel harus Text misal "08:00:00"
            'end_time'   => $row['end_time'],
            'room'       => $row['room'],
        ]);
    }
}
