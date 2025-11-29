<?php

declare(strict_types=1);

namespace App\QrCode;

use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class QrCodeResponseFactory
{
    private const DEFAULT_WRITER = 'png';

    public function __construct(private readonly BuilderInterface $builder)
    {
    }

    public function createResponse(
        string $text,
        string $filename,
        string $writerByName = self::DEFAULT_WRITER,
        bool $download = false,
    ): QrCodeResponse {
        $response = new QrCodeResponse($this->getQrContent($text, $writerByName));

        if ($download) {
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                \sprintf('QR-%s.%s', Urlizer::urlize($filename), $writerByName)
            );

            $response->headers->set('Content-Disposition', $disposition);
        }

        return $response;
    }

    public function getQrContent(string $text, string $writerByName = self::DEFAULT_WRITER): ResultInterface
    {
        $writer = match ($writerByName) {
            'svg' => new SvgWriter(),
            default => new PngWriter(),
        };

        return $this->builder->build($writer, data: $text);
    }
}
