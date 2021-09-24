<?php

namespace App\QrCode;

use Endroid\QrCode\Factory\QrCodeFactory as BaseQrCodeFactory;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Response\QrCodeResponse;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class QrCodeResponseFactory
{
    private const DEFAULT_WRITER = 'png';

    private $qrCodeFactory;

    public function __construct(BaseQrCodeFactory $qrCodeFactory)
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function createResponse(
        string $text,
        string $filename,
        string $writerByName = self::DEFAULT_WRITER
    ): QrCodeResponse {
        $response = new QrCodeResponse($this->getQrContent($text, $writerByName));

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('QR-%s.%s', Urlizer::urlize($filename), $writerByName)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getQrContent(string $text, string $writerByName): QrCodeInterface
    {
        return $this->qrCodeFactory->create($text, [
            'writer' => $writerByName,
        ]);
    }
}
