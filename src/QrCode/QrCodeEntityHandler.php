<?php

namespace App\QrCode;

use App\Entity\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeEntityHandler
{
    private $qrCodeFactory;
    private $urlGenerator;

    public function __construct(QrCodeResponseFactory $qrCodeFactory, UrlGeneratorInterface $urlGenerator)
    {
        $this->qrCodeFactory = $qrCodeFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function generateQrCode(QrCode $qrCode, string $writerByName, bool $download = false): QrCodeResponse
    {
        $redirectUrl = $this->urlGenerator->generate(
            'app_qr_code',
            [
                'uuid' => $qrCode->getUuid()->toString(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->qrCodeFactory->createResponse($redirectUrl, $qrCode->getName(), $writerByName, $download);
    }
}
