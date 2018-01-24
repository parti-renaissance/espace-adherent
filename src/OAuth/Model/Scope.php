<?php

namespace AppBundle\OAuth\Model;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MyCLabs\Enum\Enum;

/**
 * @method static WEB_HOOK()
 * @method static WRITE_USERS()
 * @method static READ_USERS()
 * @method static READ_UNSUBCRIPTIONS()
 */
final class Scope extends Enum implements ScopeEntityInterface
{
    public const WEB_HOOK = 'web_hook';
    public const WRITE_USERS = 'write:users';
    public const READ_USERS = 'read:users';
    public const READ_UNSUBCRIPTIONS = 'read:unsubscriptions';

    public function __toString()
    {
        return (string) $this->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->getValue();
    }
}
