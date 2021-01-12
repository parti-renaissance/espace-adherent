<?php

namespace App\WebHook;

use App\Membership\UserEvents;
use MyCLabs\Enum\Enum;

/**
 * @method static Event USER_CREATION()
 * @method static Event USER_MODIFICATION()
 * @method static Event USER_DELETION()
 * @method static Event USER_UPDATE_SUBSCRIPTIONS()
 */
class Event extends Enum
{
    public const USER_CREATION = UserEvents::USER_CREATED;
    public const USER_MODIFICATION = UserEvents::USER_UPDATED;
    public const USER_DELETION = UserEvents::USER_DELETED;
    public const USER_UPDATE_SUBSCRIPTIONS = UserEvents::USER_UPDATE_SUBSCRIPTIONS;
}
