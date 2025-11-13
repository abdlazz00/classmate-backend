<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportStudents implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pastikan header excel: name, email, nim, class_name, phone

        $user = User::create([
            'name'       => $row['name'],
            'email'      => $row['email'],
            'nim'        => $row['nim'],
            'class_name' => $row['class_name'],
            'phone'      => $row['phone'],
            // Password default diset sama dengan NIM agar mudah
            'password'   => Hash::make($row['nim']),
        ]);

        $user->assignRole('mahasiswa');

        return $user;
    }
}
