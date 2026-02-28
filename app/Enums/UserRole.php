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

    // ── Role Groups for Access Control ───────────────

    /**
     * Roles that can access Reception functions (Guest Card, Reservation, Check-In).
     */
    public static function receptionRoles(): array
    {
        return [self::SuperAdmin, self::FoManager, self::Receptionist, self::Instructor, self::Student];
    }

    /**
     * Roles that can access FO Cashier functions (Check-Out, Invoicing, Billing).
     */
    public static function cashierRoles(): array
    {
        return [self::SuperAdmin, self::FoManager, self::FoCashier, self::Instructor, self::Student];
    }

    /**
     * Roles that can access Night Audit functions.
     */
    public static function nightAuditRoles(): array
    {
        return [self::SuperAdmin, self::FoManager, self::NightAuditor, self::Instructor, self::Student];
    }

    /**
     * Roles that can access HK Supervisor functions (reports, linen, L&F, OOO).
     */
    public static function hkSupervisorRoles(): array
    {
        return [self::SuperAdmin, self::HkSupervisor, self::Instructor, self::Student];
    }

    /**
     * Roles that can access Sales Manager functions (contract rates, budgets, discounts).
     */
    public static function salesManagerRoles(): array
    {
        return [self::SuperAdmin, self::SalesManager, self::Instructor, self::Student];
    }
}
