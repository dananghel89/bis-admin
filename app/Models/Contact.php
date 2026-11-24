<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A message sent from the landing page contact form.
 */
class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'subject', 'message', 'ip'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
