<?php

declare(strict_types=1);

namespace App\Ses\Unsubscribe;

use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UnsubscribeUrlGenerator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $secret,
    ) {
    }

    public function generate(string $uuid): string
    {
        $token = JWT::encode(['uuid' => $uuid], $this->secret, 'HS256');

        return $this->urlGenerator->generate(
            'app_renaissance_email_unsubscribe',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
