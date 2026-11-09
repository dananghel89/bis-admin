<?php

namespace App\Models;

use Database\Factories\InfoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    /** @use HasFactory<InfoFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'author', 'status',
    ];

    /**
     * The user who wrote this info. Not named author(): that's the column holding the user id.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authorUser()
    {
        return $this->belongsTo(User::class, 'author');
    }

    /**
     * Limit the query to the infos this user may read: editors see every info,
     * everyone else only the public ones.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, $user)
    {
        if ($user->can('editPosts')) {
            return $query;
        }

        return $query->where('status', 'public');
    }
}
