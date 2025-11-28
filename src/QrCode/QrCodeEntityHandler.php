<?php

declare(strict_types=1);

namespace App\QrCode;

use App\Entity\QrCode;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeEntityHandler
{
    public function __construct(
        private readonly QrCodeResponseFactory $qrCodeFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generateQrCode(QrCode $qrCode, string $writerByName, bool $download = false): QrCodeResponse
    {
        $redirectUrl = $this->urlGenerator->generate(
            $this->getRedirectUrlRouteName($qrCode),
            ['uuid' => $qrCode->getUuid()->toString()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->qrCodeFactory->createResponse($redirectUrl, $qrCode->getName(), $writerByName, $download);
    }

    private function getRedirectUrlRouteName(QrCode $qrCode): string
    {
        return match ($qrCode->getHost()) {
            QrCodeHostEnum::HOST_ENMARCHE => 'app_qr_code',
            QrCodeHostEnum::HOST_RENAISSANCE => 'renaissance_qr_code',
            default => throw new \InvalidArgumentException(\sprintf('Unknown QrCode host "%s" for uuid: "%s".', $qrCode->getHost(), $qrCode->getUuid())),
        };
    }
}
