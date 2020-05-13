<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

final class AuthorizationCode implements AuthCodeEntityInterface
{
    use EntityTrait;
    use AuthCodeTrait;
    use TokenEntityTrait;
}
