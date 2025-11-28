<?php

declare(strict_types=1);

namespace App\OAuth\AuthorizationValidators;

use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class JsonWebTokenValidator extends BearerTokenValidator
{
    private $accessTokenRepository;

    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        parent::__construct($accessTokenRepository);

        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function validateAuthorization(ServerRequestInterface $request)
    {
        $request = parent::validateAuthorization($request);

        $accessToken = $this->accessTokenRepository->findAccessToken($request->getAttribute('oauth_access_token_id'));
        $device = $accessToken->getDevice();

        return $request
            ->withAttribute('oauth_device_id', $device ? $device->getIdentifier() : null)
            ->withAttribute('oauth_client_code', $accessToken->getClient()->getCode())
        ;
    }
}
