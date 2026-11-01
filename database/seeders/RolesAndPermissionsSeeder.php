<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'patient.register',
            'patient.view',
            'patient.edit',
            'patient.merge',
            'encounter.create',
            'encounter.view',
            'encounter.void',
            'triage.record',
            'triage.assign',
            'consultation.start',
            'consultation.document',
            'consultation.complete',
            'consultation.amend',
            'eyeclinic.examine',
            'eyeclinic.prescribe',
            'prescription.create',
            'pharmacy.dispense',
            'pharmacy.view_queue',
            'referral.create',
            'referral.view',
            'invoice.create',
            'invoice.view',
            'invoice.void',
            'payment.receive',
            'payment.view',
            'payment.reverse',
            'inventory.view',
            'inventory.receive',
            'inventory.adjust',
            'inventory.transfer',
            'user.manage',
            'role.manage',
            'branch.manage',
            'audit.view',
            'report.branch',
            'report.cross_branch',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Roles definition with permissions
        $rolePermissions = [
            'super_admin' => $permissions,
            'branch_admin' => [
                'patient.register', 'patient.view', 'patient.edit',
                'encounter.create', 'encounter.view', 'encounter.void',
                'triage.record', 'triage.assign',
                'consultation.start', 'consultation.document', 'consultation.complete', 'consultation.amend',
                'eyeclinic.examine', 'eyeclinic.prescribe',
                'prescription.create', 'pharmacy.view_queue',
                'referral.create', 'referral.view',
                'invoice.create', 'invoice.view', 'invoice.void',
                'payment.receive', 'payment.view', 'payment.reverse',
                'inventory.view', 'inventory.receive', 'inventory.adjust', 'inventory.transfer',
                'user.manage', 'audit.view', 'report.branch',
            ],
            'receptionist' => [
                'patient.register', 'patient.view', 'patient.edit',
                'encounter.create', 'encounter.view',
                'invoice.view',
            ],
            'nurse' => [
                'patient.view',
                'encounter.view',
                'triage.record', 'triage.assign',
            ],
            'doctor' => [
                'patient.view',
                'encounter.view',
                'consultation.start', 'consultation.document', 'consultation.complete', 'consultation.amend',
                'prescription.create',
                'referral.create', 'referral.view',
                'invoice.view',
            ],
            'pharmacist' => [
                'patient.view',
                'encounter.view',
                'pharmacy.dispense', 'pharmacy.view_queue',
                'inventory.view',
            ],
            'cashier' => [
                'patient.view',
                'encounter.view',
                'invoice.create', 'invoice.view',
                'payment.receive', 'payment.view',
            ],
            'optometrist' => [
                'patient.view',
                'encounter.view',
                'eyeclinic.examine', 'eyeclinic.prescribe',
                'referral.create', 'referral.view',
                'invoice.view', 'inventory.view',
            ],
            'inventory_officer' => [
                'inventory.view', 'inventory.receive', 'inventory.adjust', 'inventory.transfer',
            ],
            'records_officer' => [
                'patient.register', 'patient.view', 'patient.edit', 'patient.merge',
                'encounter.view',
            ],
            'auditor' => [
                'encounter.view', 'invoice.view', 'payment.view', 'audit.view', 'report.branch',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($perms);
        }
    }
}

