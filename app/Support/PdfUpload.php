<?php

namespace App\Support;

/**
 * Limits, rules and messages for the plan and license PDF uploads.
 */
class PdfUpload
{
    /** The app's own limit; PHP's upload limits may be lower (see maxKilobytes). */
    public const MAX_KILOBYTES = 20480;

    /**
     * The largest PDF that can really arrive: the app limit, or PHP's
     * upload_max_filesize / post_max_size when they are lower.
     */
    public static function maxKilobytes(): int
    {
        $limits = array_filter([
            self::MAX_KILOBYTES,
            self::toKilobytes((string) ini_get('upload_max_filesize')),
            self::toKilobytes((string) ini_get('post_max_size')),
        ]);

        return (int) min($limits);
    }

    /**
     * The limit for people, e.g. "2 MB".
     */
    public static function maxForHumans(): string
    {
        return round(self::maxKilobytes() / 1024, 1).' MB';
    }

    /**
     * @return list<string>
     */
    public static function rules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.self::maxKilobytes()];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        $max = self::maxForHumans();

        return [
            'property_id.required' => 'Choose a property.',
            'file.required' => 'Choose a PDF file.',
            'file.mimes' => 'The file must be a PDF.',
            'file.mimetypes' => 'The file must be a PDF.',
            'file.max' => "The PDF can be at most {$max}.",
            'file.uploaded' => "The file didn't arrive: it may be larger than the {$max} the server allows.",
        ];
    }

    /**
     * "2M" → 2048, "512K" → 512, "1G" → 1048576, "1048576" (bytes) → 1024.
     */
    public static function toKilobytes(string $value): int
    {
        $value = trim($value);
        $number = (float) $value;

        return (int) match (strtolower(substr($value, -1))) {
            'g' => $number * 1048576,
            'm' => $number * 1024,
            'k' => $number,
            default => $number / 1024,
        };
    }
}
