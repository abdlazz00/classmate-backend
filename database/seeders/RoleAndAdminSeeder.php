<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name'=>'mahasiswa']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@classmate.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin1!'),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
