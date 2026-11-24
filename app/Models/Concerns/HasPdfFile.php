<?php

namespace App\Models\Concerns;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A PDF that belongs to a property (plans and licenses). The file is on the
 * private `local` disk under file_path, and is deleted with the record.
 */
trait HasPdfFile
{
    public static function bootHasPdfFile(): void
    {
        static::deleted(function ($model) {
            $model->deleteFile();
        });
    }

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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function deleteFile(): void
    {
        if ($this->file_path) {
            Storage::disk('local')->delete($this->file_path);
        }
    }

    /**
     * The file size for people, e.g. "1.4 MB".
     */
    public function fileSizeForHumans(): string
    {
        $size = (int) $this->file_size;

        return match (true) {
            $size >= 1048576 => round($size / 1048576, 1).' MB',
            $size >= 1024 => round($size / 1024).' KB',
            default => $size.' B',
        };
    }
}
