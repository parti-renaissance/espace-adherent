<?php

namespace App\Subscription;

use MyCLabs\Enum\Enum;

final class SubscriptionTypeEnum extends Enum
{
    public const LOCAL_HOST_EMAIL = 'subscribed_emails_local_host';

    public const MOVEMENT_INFORMATION_EMAIL = 'subscribed_emails_movement_information';
    public const WEEKLY_LETTER_EMAIL = 'subscribed_emails_weekly_letter';

    public const DEPUTY_EMAIL = 'deputy_email';
    public const REFERENT_EMAIL = 'subscribed_emails_referents';
    public const CANDIDATE_EMAIL = 'candidate_email';
    public const SENATOR_EMAIL = 'senator_email';
    public const THEMATIC_COMMUNITY_EMAIL = 'thematic_community_email';

    public const MILITANT_ACTION_SMS = 'militant_action_sms';

    public const DEFAULT_EMAIL_TYPES = [
        self::LOCAL_HOST_EMAIL,
        self::MOVEMENT_INFORMATION_EMAIL,
        self::WEEKLY_LETTER_EMAIL,
        self::DEPUTY_EMAIL,
        self::SENATOR_EMAIL,
        self::REFERENT_EMAIL,
        self::CANDIDATE_EMAIL,
    ];

    public const USER_TYPES = [
        self::MILITANT_ACTION_SMS,
        self::MOVEMENT_INFORMATION_EMAIL,
        self::WEEKLY_LETTER_EMAIL,
    ];

    public const DEFAULT_MOBILE_TYPES = [
        self::MILITANT_ACTION_SMS,
    ];

    public const ADHERENT_TYPES = [
        self::MILITANT_ACTION_SMS,
        self::LOCAL_HOST_EMAIL,
        self::MOVEMENT_INFORMATION_EMAIL,
        self::WEEKLY_LETTER_EMAIL,
        self::DEPUTY_EMAIL,
        self::REFERENT_EMAIL,
        self::CANDIDATE_EMAIL,
        self::SENATOR_EMAIL,
    ];
}
