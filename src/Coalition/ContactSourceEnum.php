<?php

namespace App\Coalition;

use MyCLabs\Enum\Enum;

class ContactSourceEnum extends Enum
{
    public const ADHERENT = 'adhérent';
    public const COALITION_USER = 'utilisateur coalition';
    public const FOLLOWER = 'soutien';
}
