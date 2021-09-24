<?php

namespace App\Security;

use App\Entity\Administrator;
use App\QrCode\QrCodeResponseFactory as BaseQrCodeResponseFactory;
use Endroid\QrCode\Response\QrCodeResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class QrCodeResponseFactory
{
    private $qrCodeFactory;
    private $googleAuthenticator;

    public function __construct(BaseQrCodeResponseFactory $qrCodeFactory, GoogleAuthenticator $googleAuthenticator)
    {
        $this->qrCodeFactory = $qrCodeFactory;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    public function createResponseFor(Administrator $administrator): QrCodeResponse
    {
        $qrContent = $this->googleAuthenticator->getQRContent($administrator);

        return $this->qrCodeFactory->createResponse($qrContent, $administrator->getEmailAddress());
    }
}
