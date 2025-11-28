<?php

declare(strict_types=1);

namespace App\OAuth\ResponseType;

use App\OAuth\Model\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class SessionBearerResponse extends BearerTokenResponse
{
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        if ($accessToken instanceof AccessToken && $accessToken->currentSessionUuid) {
            return ['id_token' => $accessToken->currentSessionUuid];
        }

        return parent::getExtraParams($accessToken);
    }
}
