<?php

namespace AppBundle\Security;

use Endroid\QrCode\Factory\QrCodeFactory;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Response\QrCodeResponse;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class QrCodeResponseFactory
{
    private $qrCodeFactory;
    private $googleAuthenticator;

    public function __construct(QrCodeFactory $qrCodeFactory, GoogleAuthenticator $googleAuthenticator)
    {
        $this->qrCodeFactory = $qrCodeFactory;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    public function createResponseFor(TwoFactorInterface $user): QrCodeResponse
    {
        return new QrCodeResponse($this->getQrCode($user));
    }

    private function getQrCode(TwoFactorInterface $user): QrCodeInterface
    {
        return $this->qrCodeFactory->create($this->googleAuthenticator->getQRContent($user));
    }
}
