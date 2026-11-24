<?php

namespace App\Models;

use App\Models\Concerns\HasPdfFile;
use Database\Factories\LicenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A license (PDF) of a property, with its number and dates.
 */
class License extends Model
{
    /** @use HasFactory<LicenseFactory> */
    use HasFactory, HasPdfFile;

    /** A license expiring within this many days is "expiring soon". */
    public const EXPIRING_DAYS = 30;

    protected $fillable = [
        'property_id', 'title', 'number', 'issued_at', 'expires_at',
        'file_path', 'file_name', 'file_size', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'file_size' => 'integer',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(today());
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at !== null
            && ! $this->isExpired()
            && $this->expires_at->lte(today()->addDays(self::EXPIRING_DAYS));
    }

    /**
     * Expired, or expiring within EXPIRING_DAYS.
     *
     * @param  Builder<License>  $query
     */
    public function scopeNeedsAttention(Builder $query): void
    {
        $query->whereNotNull('expires_at')->whereDate('expires_at', '<=', today()->addDays(self::EXPIRING_DAYS));
    }
}
