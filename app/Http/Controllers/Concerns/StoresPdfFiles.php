<?php

namespace App\Http\Controllers\Concerns;

use App\Models\License;
use App\Models\Plan;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Plans and licenses: PDFs on the private `local` disk, downloaded only through
 * the app by users who can see their property.
 */
trait StoresPdfFiles
{
    /**
     * Store an uploaded PDF in $folder under a random name.
     *
     * @return array{file_path: string, file_name: string, file_size: int}
     */
    protected function storePdf(UploadedFile $file, string $folder): array
    {
        $name = Str::limit(basename($file->getClientOriginalName()), 200, '');

        return [
            'file_path' => $file->storeAs($folder, Str::random(40).'.pdf', 'local'),
            'file_name' => Str::endsWith(strtolower($name), '.pdf') ? $name : $name.'.pdf',
            'file_size' => (int) $file->getSize(),
        ];
    }

    /**
     * Send the PDF with its original name, or a 404 when this user can't see its
     * property or the file is missing.
     */
    protected function downloadPdf(Plan|License $model, User $user): StreamedResponse
    {
        abort_unless($model->property->isVisibleTo($user), 404);
        abort_unless(Storage::disk('local')->exists($model->file_path), 404);

        return Storage::disk('local')->download($model->file_path, $model->file_name, ['Content-Type' => 'application/pdf']);
    }

    /**
     * The visible properties as [id => name], for the filters.
     *
     * @return array<int, string>
     */
    protected function propertyOptions(User $user): array
    {
        return Property::visibleTo($user)->orderBy('name')->pluck('name', 'id')->all();
    }
}
