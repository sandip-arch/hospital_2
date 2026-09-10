<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            'superadmin' => ['display_name' => 'Super Administrator', 'description' => 'Full system access, security governance, and configurations'],
            'admin' => ['display_name' => 'Hospital Admin', 'description' => 'Hospital manager and operational administrator'],
            'doctor' => ['display_name' => 'Doctor / Physician', 'description' => 'Clinical diagnosis, prescribing, and patient care'],
            'staff' => ['display_name' => 'Hospital Staff', 'description' => 'Support staff (Reception, Nursing, Lab, Pharmacy, Billing)'],
            'patient' => ['display_name' => 'Patient', 'description' => 'Patient portal user for appointments, records, and billing'],
            'driver' => ['display_name' => 'Ambulance Driver', 'description' => 'Emergency response and patient transport ambulance driver'],
            'user' => ['display_name' => 'General / Guest User', 'description' => 'Unverified or registered visitor on public portal'],
        ];

        $roleModels = [];
        foreach ($roles as $name => $data) {
            $roleModels[$name] = Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                ]
            );
        }

        // 2. Create Permissions
        $permissions = [
            // System & RBAC
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Configure hospital system parameters'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Create, edit, and manage user accounts and roles'],
            ['name' => 'View Audit Logs', 'slug' => 'view_audit_logs', 'description' => 'View system activity and security audit trail'],
            ['name' => 'Manage Departments', 'slug' => 'manage_departments', 'description' => 'Setup and edit hospital departments'],
            ['name' => 'Manage Facilities', 'slug' => 'manage_facilities', 'description' => 'Manage rooms, beds, and ward units'],
            
            // Patient & Appointments
            ['name' => 'Register Patients', 'slug' => 'register_patients', 'description' => 'Register new patients and emergency contacts'],
            ['name' => 'View Patients', 'slug' => 'view_patients', 'description' => 'View patient profiles and medical records'],
            ['name' => 'Manage Appointments', 'slug' => 'manage_appointments', 'description' => 'Book, reschedule, and cancel appointments'],
            ['name' => 'Set Doctor Schedule', 'slug' => 'set_doctor_schedule', 'description' => 'Manage doctor shifts and availability'],
            
            // Clinical
            ['name' => 'Create Medical Records', 'slug' => 'create_medical_records', 'description' => 'Create consultation SOAP notes and diagnoses'],
            ['name' => 'Prescribe Medicines', 'slug' => 'prescribe_medicines', 'description' => 'Issue digital prescriptions to patients'],
            ['name' => 'Order Lab Tests', 'slug' => 'order_lab_tests', 'description' => 'Order diagnostic and laboratory tests'],
            ['name' => 'Process Lab Reports', 'slug' => 'process_lab_reports', 'description' => 'Input lab test results and attach reports'],
            
            // Facilities & Pharmacy & Billing
            ['name' => 'Admit Patients', 'slug' => 'admit_patients', 'description' => 'Authorize and assign beds for inpatient admissions'],
            ['name' => 'Dispense Medicines', 'slug' => 'dispense_medicines', 'description' => 'Fulfill prescriptions and manage pharmacy stock'],
            ['name' => 'Manage Pharmacy Stock', 'slug' => 'manage_pharmacy_stock', 'description' => 'Add and adjust medicine inventory'],
            ['name' => 'Manage Billing', 'slug' => 'manage_billing', 'description' => 'Create invoices and collect payments'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'View clinical and financial analytics reports'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'description' => $perm['description'],
                ]
            );
        }

        // 3. Assign Permissions to Roles
        // Superadmin gets all permissions
        $roleModels['superadmin']->permissions()->sync(Permission::all());

        // Admin
        $adminPerms = Permission::whereIn('slug', [
            'manage_settings', 'manage_users', 'view_audit_logs', 'manage_departments',
            'manage_facilities', 'register_patients', 'view_patients', 'manage_appointments',
            'admit_patients', 'dispense_medicines', 'manage_pharmacy_stock', 'manage_billing', 'view_reports'
        ])->get();
        $roleModels['admin']->permissions()->sync($adminPerms);

        // Doctor
        $doctorPerms = Permission::whereIn('slug', [
            'view_patients', 'manage_appointments', 'set_doctor_schedule',
            'create_medical_records', 'prescribe_medicines', 'order_lab_tests', 'admit_patients'
        ])->get();
        $roleModels['doctor']->permissions()->sync($doctorPerms);

        // Staff
        $staffPerms = Permission::whereIn('slug', [
            'register_patients', 'view_patients', 'manage_appointments',
            'process_lab_reports', 'admit_patients', 'dispense_medicines', 'manage_pharmacy_stock', 'manage_billing'
        ])->get();
        $roleModels['staff']->permissions()->sync($staffPerms);
    }
}
