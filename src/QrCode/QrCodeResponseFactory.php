<?php

namespace App\QrCode;

use Endroid\QrCode\Factory\QrCodeFactory as BaseQrCodeFactory;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Response\QrCodeResponse;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class QrCodeResponseFactory
{
    private $qrCodeFactory;

    public function __construct(BaseQrCodeFactory $qrCodeFactory)
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function createResponse(string $text, string $filename): QrCodeResponse
    {
        $response = new QrCodeResponse($this->getQrContent($text));

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('QR-%s.svg', Urlizer::urlize($filename))
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getQrContent(string $text): QrCodeInterface
    {
        return $this->qrCodeFactory->create($text, [
            'writer' => 'svg',
        ]);
    }
}
