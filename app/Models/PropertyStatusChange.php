<?php

namespace App\Models;

use Database\Factories\PropertyStatusChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One change of a property's work status: who, when, from, to and a note.
 */
class PropertyStatusChange extends Model
{
    /** @use HasFactory<PropertyStatusChangeFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['property_id', 'user_id', 'from_status', 'to_status', 'note'];

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
