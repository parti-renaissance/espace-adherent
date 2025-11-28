<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Administrator;
use App\QrCode\QrCodeResponseFactory as BaseQrCodeResponseFactory;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class QrCodeResponseFactory
{
    public function __construct(
        private readonly BaseQrCodeResponseFactory $qrCodeFactory,
        private readonly GoogleAuthenticator $googleAuthenticator,
    ) {
    }

    public function createResponseFor(Administrator $administrator): QrCodeResponse
    {
        return $this->qrCodeFactory->createResponse(
            $this->googleAuthenticator->getQRContent($administrator),
            $administrator->getEmailAddress()
        );
    }
}
