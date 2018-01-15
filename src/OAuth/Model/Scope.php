<?php

namespace AppBundle\OAuth\Model;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class Scope implements ScopeEntityInterface
{
    use EntityTrait;

    public function __construct(string $identifier)
    {
        $this->setIdentifier($identifier);
    }

    public function __toString()
    {
        return (string) $this->getIdentifier();
    }

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
