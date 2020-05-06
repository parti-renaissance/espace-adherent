<?php

namespace App\WebHook;

use App\Membership\UserEvents;
use MyCLabs\Enum\Enum;

/**
 * @method static USER_CREATION()
 * @method static USER_MODIFICATION()
 * @method static USER_DELETION()
 * @method static USER_UPDATE_SUBSCRIPTIONS()
 */
class Event extends Enum
{
    public const USER_CREATION = UserEvents::USER_CREATED;
    public const USER_MODIFICATION = UserEvents::USER_UPDATED;
    public const USER_DELETION = UserEvents::USER_DELETED;
    public const USER_UPDATE_SUBSCRIPTIONS = UserEvents::USER_UPDATE_SUBSCRIPTIONS;
}
