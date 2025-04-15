<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

final class AccessToken implements AccessTokenEntityInterface
{
    use TokenEntityTrait;
    use AccessTokenTrait;
    use EntityTrait;

    public ?string $oldAccessTokenId = null;
}
