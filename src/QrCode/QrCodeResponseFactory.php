<?php

namespace App\QrCode;

use Endroid\QrCode\Factory\QrCodeFactory as BaseQrCodeFactory;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Response\QrCodeResponse;

class QrCodeResponseFactory
{
    private $qrCodeFactory;

    public function __construct(BaseQrCodeFactory $qrCodeFactory)
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function createResponse(string $text): QrCodeResponse
    {
        return new QrCodeResponse($this->getQrContent($text));
    }

    private function getQrContent(string $text): QrCodeInterface
    {
        return $this->qrCodeFactory->create($text);
    }
}
