<?php

namespace App\Models;

use App\Models\Concerns\HasPdfFile;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A plan (PDF) of a property.
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasPdfFile;

    protected $fillable = ['property_id', 'title', 'description', 'file_path', 'file_name', 'file_size', 'uploaded_by'];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }
}
