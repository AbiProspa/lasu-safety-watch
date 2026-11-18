<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'matric_no', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Admin and responder roles can open the dashboard. */
    public function isStaff(): bool
    {
        return $this->role !== 'student';
    }

    /** The unit this user responds for (security, works, ...) or null for admin/student. */
    public function unit(): ?string
    {
        return config("lsw.roles.{$this->role}.1");
    }

    public function roleLabel(): string
    {
        return config("lsw.roles.{$this->role}.0", ucfirst($this->role));
    }

    /** Can this user see / manage a given report? */
    public function canManage(Report $report): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isStaff() && $report->category->unit === $this->unit();
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    }
}
