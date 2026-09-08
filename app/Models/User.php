<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Manager::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role helper methods
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['superadmin', 'admin']);
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isPatient(): bool
    {
        return $this->hasRole('patient');
    }

    public function primaryRole(): string
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'user';
    }

    public function primaryRoleDisplay(): string
    {
        $role = $this->roles->first();
        if (!$role) return 'User';
        return $role->display_name ?? ucfirst($role->name);
    }

    public function roleBadgeColor(): string
    {
        $role = $this->primaryRole();
        return match ($role) {
            'superadmin' => 'bg-purple-100 text-purple-800 border-purple-200',
            'admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'doctor' => 'bg-blue-100 text-blue-800 border-blue-200',
            'staff' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'patient' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
