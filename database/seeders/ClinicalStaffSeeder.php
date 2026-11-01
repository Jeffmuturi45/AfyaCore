<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClinicalStaffSeeder extends Seeder
{
    public function run(): void
    {
        $hos = Branch::where('code', 'HOS')->first();
        $nyr = Branch::where('code', 'NYR')->first();
        $krt = Branch::where('code', 'KRT')->first();

        // Ensure super_admin has role
        $superAdmin = User::where('email', 'admin@afyacore.test')->first();
        if ($superAdmin) {
            $superAdmin->assignRole('super_admin');
        }

        $staff = [
            // Afya Hospital (OPD)
            [
                'branch_id' => $hos->id,
                'name' => 'Dr. Alice Wanjiku',
                'email' => 'doctor.hos@afyacore.test',
                'role' => 'branch_admin',
            ],
            [
                'branch_id' => $hos->id,
                'name' => 'Dr. Brian Kiprop',
                'email' => 'doctor2.hos@afyacore.test',
                'role' => 'doctor',
            ],
            [
                'branch_id' => $hos->id,
                'name' => 'Nurse Grace Muthoni',
                'email' => 'nurse.hos@afyacore.test',
                'role' => 'nurse',
            ],
            [
                'branch_id' => $hos->id,
                'name' => 'Jane Mwangi',
                'email' => 'reception.hos@afyacore.test',
                'role' => 'receptionist',
            ],
            [
                'branch_id' => $hos->id,
                'name' => 'Pharm. Peter Otieno',
                'email' => 'pharmacy.hos@afyacore.test',
                'role' => 'pharmacist',
            ],
            [
                'branch_id' => $hos->id,
                'name' => 'Faith Chebet',
                'email' => 'cashier.hos@afyacore.test',
                'role' => 'cashier',
            ],

            // Prime Opticals (NYR)
            [
                'branch_id' => $nyr->id,
                'name' => 'Optom. Daniel Kariuki',
                'email' => 'optometrist.nyr@afyacore.test',
                'role' => 'optometrist',
            ],
            [
                'branch_id' => $nyr->id,
                'name' => 'Lucy Njeri',
                'email' => 'reception.nyr@afyacore.test',
                'role' => 'receptionist',
            ],

            // Best Wing Opticals (KRT)
            [
                'branch_id' => $krt->id,
                'name' => 'Optom. Sarah Achieng',
                'email' => 'optometrist.krt@afyacore.test',
                'role' => 'optometrist',
            ],
            [
                'branch_id' => $krt->id,
                'name' => 'Samuel Kimani',
                'email' => 'reception.krt@afyacore.test',
                'role' => 'receptionist',
            ],
        ];

        foreach ($staff as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'branch_id' => $item['branch_id'],
                    'name' => $item['name'],
                    'password' => Hash::make('Password@2024!'),
                    'role' => $item['role'],
                    'is_active' => true,
                    'force_password_change' => false,
                ]
            );

            $user->syncRoles([$item['role']]);
        }
    }
}
