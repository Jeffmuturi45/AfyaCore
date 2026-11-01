<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only the super admin is seeded — everything else created via UI
        User::create([
            'branch_id'             => null,
            'name'                  => 'System Administrator',
            'email'                 => 'admin@afyacore.test',
            'password'              => Hash::make('Admin@2024!Afya'),
            'role'                  => 'super_admin',
            'is_active'             => true,
            'force_password_change' => false,
        ]);
    }
}