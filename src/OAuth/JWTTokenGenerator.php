<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JWTTokenGenerator
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function generate(Adherent $user, Client $client): string
    {
        $payload = [
            'email' => $user->getEmailAddress(),
            'name' => $user->getFullName(),
            'locale' => 'fr',
        ];

        if ($user->getImagePath()) {
            $payload['profilePicture'] = $this->urlGenerator->generate('asset_url', ['path' => $user->getImagePath()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return JWT::encode($payload, $client->getSecret(), 'HS256');
    }
}
