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

    public function generate(string $uuid, ?int $memberId = null, ?string $messageUuid = null): string
    {
        $payload = ['uuid' => $uuid];
        if (null !== $memberId) {
            $payload['member_id'] = $memberId;
        }
        if (null !== $messageUuid) {
            $payload['message_uuid'] = $messageUuid;
        }

        $token = JWT::encode($payload, $this->secret, 'HS256');

        return $this->urlGenerator->generate(
            'app_renaissance_email_unsubscribe',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
