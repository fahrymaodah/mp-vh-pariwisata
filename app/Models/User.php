<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_code',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ── Filament ─────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => in_array($this->role, [UserRole::SuperAdmin, UserRole::Instructor]),
            'fo' => in_array($this->role, [UserRole::SuperAdmin, UserRole::FoManager, UserRole::Receptionist, UserRole::FoCashier, UserRole::NightAuditor, UserRole::Instructor, UserRole::Student]),
            'hk' => in_array($this->role, [UserRole::SuperAdmin, UserRole::HkSupervisor, UserRole::RoomAttendant, UserRole::Instructor, UserRole::Student]),
            'sales' => in_array($this->role, [UserRole::SuperAdmin, UserRole::Sales, UserRole::SalesManager, UserRole::Instructor, UserRole::Student]),
            'telop' => in_array($this->role, [UserRole::SuperAdmin, UserRole::TelephoneOperator, UserRole::Instructor, UserRole::Student]),
            default => false,
        };
    }

    // ── Relationships ────────────────────────────────

    public function reservationsCreated(): HasMany
    {
        return $this->hasMany(Reservation::class, 'created_by');
    }

    // ── Helper Methods ───────────────────────────────

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }
}
