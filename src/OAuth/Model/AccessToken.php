<?php

declare(strict_types=1);

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Symfony\Component\Uid\Uuid;

final class AccessToken implements AccessTokenEntityInterface
{
    use TokenEntityTrait;
    use AccessTokenTrait;
    use EntityTrait;

    public ?string $oldAccessTokenId = null;
    public ?Uuid $currentSessionUuid = null;
    public ?string $nonce = null;
}
