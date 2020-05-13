<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

final class User implements UserEntityInterface
{
    use EntityTrait;

    public function __construct(string $identifier)
    {
        $this->setIdentifier($identifier);
    }
}
