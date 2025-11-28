<?php

declare(strict_types=1);

namespace App\Image;

class PdfToImageConverter
{
    public function getRawImageFromPdf(string $pdfContent, string $format = 'jpg', int $page = 0): string
    {
        $imagick = new \Imagick();
        $imagick->readImageBlob($pdfContent);
        $imagick->setIteratorIndex($page);
        $imagick->setImageFormat($format);
        $imagick->setCompressionQuality(100);

        return (string) $imagick;
    }
}
