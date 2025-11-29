<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Repository\OAuth\AccessTokenRepository;
use App\Repository\OAuth\RefreshTokenRepository;

class TokenRevocationAuthority
{
    public function __construct(
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
    ) {
    }

    public function revokeClientTokens(Client $client): void
    {
        $this->accessTokenRepository->revokeClientTokens($client);
        $this->refreshTokenRepository->revokeClientTokens($client);
    }

    public function revokeUserTokens(Adherent $user): void
    {
        if (!$user->getId()) {
            return;
        }

        $this->accessTokenRepository->revokeUserTokens($user);
        $this->refreshTokenRepository->revokeUserTokens($user);
    }
}
