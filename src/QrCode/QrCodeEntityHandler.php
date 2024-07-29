<?php

namespace App\QrCode;

use App\Entity\QrCode;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
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
            $this->getRedirectUrlRouteName($qrCode),
            ['uuid' => $qrCode->getUuid()->toString()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->qrCodeFactory->createResponse($redirectUrl, $qrCode->getName(), $writerByName, $download);
    }

    private function getRedirectUrlRouteName(QrCode $qrCode): string
    {
        switch ($qrCode->getHost()) {
            case QrCodeHostEnum::HOST_ENMARCHE:
                return 'app_qr_code';
            case QrCodeHostEnum::HOST_AVECVOUS:
                return 'avecvous_qr_code';
            case QrCodeHostEnum::HOST_RENAISSANCE:
                return 'renaissance_qr_code';
            default:
                throw new \InvalidArgumentException(\sprintf('Unknow QrCode host "%s" for uuid: "%s".', $qrCode->getHost(), $qrCode->getUuid()));
        }
    }
}
