<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent\JEM;

use App\Admin\NationalEvent\NationalEventInscriptionsAdmin;
use App\NationalEvent\NationalEventTypeEnum;

class JEMNationalEventInscriptionsAdmin extends NationalEventInscriptionsAdmin
{
    protected $baseRoutePattern = 'meetings-jem/inscriptions';
    protected $baseRouteName = 'admin_app_nationalevent_nationalevent_jem_inscriptions';

    protected function getAllowedEventTypes(): ?array
    {
        return [NationalEventTypeEnum::JEM];
    }

    protected function getForbiddenEventTypes(): ?array
    {
        return null;
    }
}
