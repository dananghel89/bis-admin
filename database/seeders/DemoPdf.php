<?php

namespace Database\Seeders;

/**
 * A tiny one-page PDF with a few lines of text, so the demo downloads open.
 */
class DemoPdf
{
    /**
     * @param  list<string>  $lines
     */
    public static function make(array $lines): string
    {
        $text = "BT /F1 18 Tf 72 760 Td 22 TL\n";
        foreach ($lines as $i => $line) {
            // PDF strings escape \, ( and ); Helvetica only has Latin-1, so accents are dropped.
            $line = (string) (@iconv('UTF-8', 'ASCII//TRANSLIT', $line) ?: $line);
            $line = strtr($line, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);
            $text .= ($i === 0 ? '' : 'T* ').'('.$line.") Tj\n";
            if ($i === 0) {
                $text .= "/F1 12 Tf\n";
            }
        }
        $text .= 'ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($text)." >>\nstream\n".$text."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n".'0 '.(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";

        return $pdf;
    }
}
