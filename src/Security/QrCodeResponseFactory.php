<?php

namespace App\Security;

use App\QrCode\QrCodeResponseFactory as BaseQrCodeResponseFactory;
use Endroid\QrCode\Response\QrCodeResponse;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
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

    public function createResponseFor(TwoFactorInterface $user): QrCodeResponse
    {
        $qrContent = $this->googleAuthenticator->getQRContent($user);

        return $this->qrCodeFactory->createResponse($qrContent);
    }
}
