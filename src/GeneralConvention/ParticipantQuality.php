<?php

namespace App\GeneralConvention;

enum ParticipantQuality: string
{
    case ADHERENT = 'adherent';
    case SYMPATHIZER = 'sympathizer';
    case ADHERENT_BEFORE = 'adherent_before';
}
