<?php

namespace App\Adherent\Referral;

enum TypeEnum: string
{
    case LINK = 'link';
    case INVITATION = 'invitation';
    case PREREGISTRATION = 'preregistration';
}
