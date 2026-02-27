<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@parhotel.test', 'role' => UserRole::SuperAdmin, 'employee_code' => 'ADM001'],
            ['name' => 'FO Manager', 'email' => 'fo.manager@parhotel.test', 'role' => UserRole::FoManager, 'employee_code' => 'FO001'],
            ['name' => 'Receptionist', 'email' => 'reception@parhotel.test', 'role' => UserRole::Receptionist, 'employee_code' => 'FO002'],
            ['name' => 'FO Cashier', 'email' => 'cashier@parhotel.test', 'role' => UserRole::FoCashier, 'employee_code' => 'FO003'],
            ['name' => 'HK Supervisor', 'email' => 'hk.supervisor@parhotel.test', 'role' => UserRole::HkSupervisor, 'employee_code' => 'HK001'],
            ['name' => 'Room Attendant', 'email' => 'room.attendant@parhotel.test', 'role' => UserRole::RoomAttendant, 'employee_code' => 'HK002'],
            ['name' => 'Sales Executive', 'email' => 'sales@parhotel.test', 'role' => UserRole::Sales, 'employee_code' => 'SM001'],
            ['name' => 'Sales Manager', 'email' => 'sales.manager@parhotel.test', 'role' => UserRole::SalesManager, 'employee_code' => 'SM002'],
            ['name' => 'Telephone Operator', 'email' => 'telop@parhotel.test', 'role' => UserRole::TelephoneOperator, 'employee_code' => 'TO001'],
            ['name' => 'Night Auditor', 'email' => 'night.auditor@parhotel.test', 'role' => UserRole::NightAuditor, 'employee_code' => 'NA001'],
            ['name' => 'Instructor', 'email' => 'instructor@parhotel.test', 'role' => UserRole::Instructor, 'employee_code' => 'INS001'],
            ['name' => 'Student Trainee', 'email' => 'student@parhotel.test', 'role' => UserRole::Student, 'employee_code' => 'STU001'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'employee_code' => $userData['employee_code'],
                    'is_active' => true,
                ],
            );
        }
    }
}
