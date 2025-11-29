<?php

declare(strict_types=1);

namespace App\GeneralConvention;

enum OrganizerEnum: string
{
    case ASSEMBLY = 'assembly';
    case DISTRICT = 'district';
    case COMMITTEE = 'committee';
}
