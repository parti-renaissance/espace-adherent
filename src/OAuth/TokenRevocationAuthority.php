<?php

namespace AppBundle\OAuth;

use AppBundle\Entity\OAuth\Client;
use AppBundle\Repository\OAuth\AccessTokenRepository;
use AppBundle\Repository\OAuth\RefreshTokenRepository;

class TokenRevocationAuthority
{
    private $accessTokenRepository;
    private $refreshTokenRepository;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        RefreshTokenRepository $refreshTokenRepository
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function revokeClientTokens(Client $client): void
    {
        $this->accessTokenRepository->revokeClientTokens($client);
        $this->refreshTokenRepository->revokeClientTokens($client);
    }
}
