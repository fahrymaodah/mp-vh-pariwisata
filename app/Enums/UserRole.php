<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case FoManager = 'fo_manager';
    case Receptionist = 'receptionist';
    case FoCashier = 'fo_cashier';
    case HkSupervisor = 'hk_supervisor';
    case RoomAttendant = 'room_attendant';
    case Sales = 'sales';
    case SalesManager = 'sales_manager';
    case TelephoneOperator = 'telephone_operator';
    case NightAuditor = 'night_auditor';
    case Instructor = 'instructor';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::FoManager => 'FO Manager',
            self::Receptionist => 'Receptionist',
            self::FoCashier => 'FO Cashier',
            self::HkSupervisor => 'HK Supervisor',
            self::RoomAttendant => 'Room Attendant',
            self::Sales => 'Sales',
            self::SalesManager => 'Sales Manager',
            self::TelephoneOperator => 'Telephone Operator',
            self::NightAuditor => 'Night Auditor',
            self::Instructor => 'Instructor',
            self::Student => 'Student',
        };
    }

    public function panel(): string
    {
        return match ($this) {
            self::SuperAdmin, self::Instructor => 'admin',
            self::FoManager, self::Receptionist, self::FoCashier, self::NightAuditor => 'fo',
            self::HkSupervisor, self::RoomAttendant => 'hk',
            self::Sales, self::SalesManager => 'sales',
            self::TelephoneOperator => 'telop',
            self::Student => 'fo',
        };
    }
}
