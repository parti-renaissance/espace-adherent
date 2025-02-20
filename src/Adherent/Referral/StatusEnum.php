<?php

namespace App\Adherent\Referral;

enum StatusEnum: string
{
    case INVITATION_SENT = 'invitation_sent';
    case ACCOUNT_CREATED = 'account_created';
    case ADHESION_FINISHED = 'adhesion_finished';
    case ADHESION_VIA_OTHER_LINK = 'adhesion_via_other_link';
    case REPORTED = 'reported';
}
