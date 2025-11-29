<?php

declare(strict_types=1);

namespace App\Adherent\Referral;

enum TypeEnum: string
{
    case LINK = 'link';
    case INVITATION = 'invitation';
    case PREREGISTRATION = 'preregistration';
}
