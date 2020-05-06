<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MyCLabs\Enum\Enum;

/**
 * @method static WEB_HOOK()
 * @method static WRITE_USERS()
 * @method static READ_USERS()
 * @method static READ_UNSUBCRIPTIONS()
 * @method static READ_TYPEFORMS()
 * @method static JECOUTE_SURVEYS()
 */
final class Scope extends Enum implements ScopeEntityInterface
{
    public const WEB_HOOK = 'web_hook';
    public const WRITE_USERS = 'write:users';
    public const READ_USERS = 'read:users';
    public const READ_UNSUBCRIPTIONS = 'read:unsubscriptions';
    public const READ_TYPEFORMS = 'read:typeforms';
    public const READ_STATS = 'read:stats';
    public const JECOUTE_SURVEYS = 'jecoute_surveys';
    public const CRM_PARIS = 'crm_paris';

    public function __toString()
    {
        return (string) $this->getIdentifier();
    }

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }

    public function getIdentifier()
    {
        return $this->getValue();
    }
}
