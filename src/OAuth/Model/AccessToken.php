<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

final class AccessToken extends AbstractGrantToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait;
}
