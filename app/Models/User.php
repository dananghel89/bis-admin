<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Two roles: admin (shown as "Staff") manages everything; editor (shown as
 * "Architect") sees the properties made visible to them.
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ADMIN = 0;

    public const EDITOR = 1;

    /** The role names people see. */
    public const ROLE_LABELS = [self::ADMIN => 'Staff', self::EDITOR => 'Architect'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => 'integer',
        ];
    }

    /**
     * The properties this user is assigned to as an architect.
     *
     * @return BelongsToMany<Property, $this>
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    /**
     * @return HasOne<MailAccount, $this>
     */
    public function mailAccount(): HasOne
    {
        return $this->hasOne(MailAccount::class);
    }

    /**
     * The role in code: 'admin', 'editor', or '' for a missing or unknown role,
     * which gets no permissions (never admin).
     */
    public function getRole(): string
    {
        return match ($this->role) {
            self::ADMIN => 'admin',
            self::EDITOR => 'editor',
            default => '',
        };
    }

    /**
     * The role people see: "Staff" or "Architect".
     */
    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? '';
    }

    public function isStaff(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function isArchitect(): bool
    {
        return $this->role === self::EDITOR;
    }
}
