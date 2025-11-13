<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportCourses implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Course([
            'name' => $row['name'],
            'code' => $row['code'],
            'lecturer' => $row['lecturer'],
            'class_name' => $row['class_name'],
        ]);
    }
}
