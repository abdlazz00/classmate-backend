<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Mahasiswa', User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count())
                ->description('Jumlah mahasiswa terdaftar')
                ->color('success'),
            Stat::make('Total Mata Kuliah', Course::count())
                ->description('Jumlah mata kuliah')
                ->color('info'),
            Stat::make('Tugas Aktif', Assignment::where('deadline', '>=', now())->count())
                ->description('Tugas belum lewat deadline')
                ->color('warning'),
        ];
    }
}
