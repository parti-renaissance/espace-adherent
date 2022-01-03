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
 * @method static READ_STATS()
 * @method static JECOUTE_SURVEYS()
 * @method static JEMARCHE_APP()
 * @method static CRM_PARIS()
 * @method static READ_PROFILE()
 * @method static WRITE_PROFILE()
 */
final class Scope extends Enum implements ScopeEntityInterface
{
    public const WEB_HOOK = 'web_hook';

    public const WRITE_USERS = 'write:users';
    public const WRITE_EVENT = 'write:event';

    public const READ_USERS = 'read:users';
    public const READ_UNSUBCRIPTIONS = 'read:unsubscriptions';
    public const READ_TYPEFORMS = 'read:typeforms';
    public const READ_STATS = 'read:stats';

    public const JECOUTE_SURVEYS = 'jecoute_surveys';
    public const JEMARCHE_APP = 'jemarche_app';
    public const JEMENGAGE_ADMIN = 'jemengage_admin';
    public const CRM_PARIS = 'crm_paris';
    public const READ_PROFILE = 'read:profile';
    public const WRITE_PROFILE = 'write:profile';

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
