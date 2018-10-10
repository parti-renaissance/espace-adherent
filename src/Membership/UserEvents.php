<?php

namespace AppBundle\Membership;

final class UserEvents
{
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const USER_UPDATE_SUBSCRIPTIONS = 'user.update_subscriptions';
    public const USER_SWITCH_TO_ADHERENT = 'user.switch_to_adherent';

    private function __construct()
    {
    }
}
