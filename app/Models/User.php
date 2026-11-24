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

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ADMIN = 0;
    const EDITOR = 1;
    const SUBSCRIBER = 2;

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
     * Get role value from integer. A missing role (NULL) is no role at all, never admin.
     */
    public function getRole(): string
    {
        return match ($this->role) {
            self::ADMIN => 'admin',
            self::EDITOR => 'editor',
            self::SUBSCRIBER => 'subscriber',
            default => '',
        };
    }
}
