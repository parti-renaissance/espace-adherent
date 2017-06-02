<?php

namespace AppBundle\Security;

use GuzzleHttp\Client;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthenticationQrCodeFactory
{
    private $client;
    private $googleAuthenticator;

    public function __construct(Client $client, GoogleAuthenticator $googleAuthenticator)
    {
        $this->client = $client;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    public function createQrCodeResponse(TwoFactorInterface $user): Response
    {
        $response = $this->client->request(
            'GET',
            '/chart?'.http_build_query([
                'cht' => 'qr',
                'chs' => '300x300',
                'chld' => 'M|0',
                'chl' => $this->googleAuthenticator->getQRContent($user),
            ])
        );

        return new Response(\GuzzleHttp\Psr7\copy_to_string($response->getBody()), Response::HTTP_OK, [
            'Content-Type' => 'image/png',
        ]);
    }
}
