<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A property: the center of the data. Its plans and licenses are PDFs; its
 * architects are the editors assigned to it.
 */
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    public const STATUSES = ['draft' => 'Draft', 'published' => 'Published'];

    public const WORK_STATUSES = ['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'];

    protected $fillable = [
        'name', 'address', 'city', 'postal_code', 'latitude', 'longitude',
        'description', 'status', 'work_status', 'created_by',
    ];

    protected $attributes = [
        'status' => 'draft',
        'work_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // The database deletes the plan and license rows (cascade); their files go here.
        static::deleting(function (Property $property) {
            $property->plans->each->deleteFile();
            $property->licenses->each->deleteFile();
        });
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function architects(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<Plan, $this>
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    /**
     * Newest first.
     *
     * @return HasMany<PropertyStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(PropertyStatusChange::class)->latest('id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The properties a user may see. Staff: all. Architects: published ones that are
     * assigned to them, or that have no architect at all. Every property, plan and
     * license query for a page goes through this; anything else is a 404.
     *
     * @param  Builder<Property>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isStaff()) {
            return;
        }

        if (! $user->isArchitect()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('status', 'published')->where(function (Builder $query) use ($user) {
            $query->whereHas('architects', fn (Builder $q) => $q->whereKey($user->id))
                ->orWhereDoesntHave('architects');
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return static::visibleTo($user)->whereKey($this->id)->exists();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function workStatusLabel(): string
    {
        return self::WORK_STATUSES[$this->work_status] ?? $this->work_status;
    }

    /**
     * The full address on one line.
     */
    public function fullAddress(): string
    {
        return collect([$this->address, trim($this->postal_code.' '.$this->city)])->filter()->implode(', ');
    }
}
