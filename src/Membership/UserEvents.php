<?php

declare(strict_types=1);

namespace App\Membership;

final class UserEvents
{
    public const USER_CREATED = 'user.created';
    public const USER_VALIDATED = 'user.validated';

    public const USER_BEFORE_UPDATE = 'user.before_update';
    public const USER_UPDATED = 'user.updated';
    public const USER_UPDATE_INTERESTS = 'user.update_interests';
    public const USER_UPDATE_COMMITTEE_PRIVILEGE = 'user.update_committee_privilege';
    public const USER_UPDATED_IN_ADMIN = 'user.updated_in_admin';

    public const USER_PROFILE_BEFORE_UPDATE = 'user.profile.before_update';
    public const USER_PROFILE_AFTER_UPDATE = 'user.profile.after_update';

    public const USER_DELETED = 'user.deleted';

    public const USER_EMAIL_CHANGE_REQUEST = 'user.email_change.request';
    public const USER_EMAIL_UPDATED = 'user.email_updated';

    public const USER_FORGOT_PASSWORD = 'user.forgot_password';
    public const USER_FORGOT_PASSWORD_VALIDATED = 'user.forgot_password.validated';

    private function __construct()
    {
    }
}
