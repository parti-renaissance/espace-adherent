<?php

namespace App\Membership;

final class UserEvents
{
    public const USER_CREATED = 'user.created';
    public const USER_VALIDATED = 'user.validated';

    public const RENAISSANCE_USER_CREATED = 'renaissance.user.created';

    public const USER_BEFORE_UPDATE = 'user.before_update';
    public const USER_UPDATED = 'user.updated';
    public const USER_UPDATE_SUBSCRIPTIONS = 'user.update_subscriptions';
    public const USER_UPDATE_INTERESTS = 'user.update_interests';
    public const USER_UPDATE_COMMITTEE_PRIVILEGE = 'user.update_committee_privilege';
    public const USER_UPDATED_IN_ADMIN = 'user.updated_in_admin';

    public const USER_DELETED = 'user.deleted';
    public const USER_SWITCH_TO_ADHERENT = 'user.switch_to_adherent';
    public const USER_EMAIL_UPDATED = 'user.email_updated';

    public const USER_FORGOT_PASSWORD = 'user.forgot_password';

    private function __construct()
    {
    }
}
