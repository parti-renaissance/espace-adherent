<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent\JEM;

use App\Admin\NationalEvent\NationalEventAdmin;
use App\NationalEvent\NationalEventTypeEnum;

class JEMNationalEventAdmin extends NationalEventAdmin
{
    protected $baseRoutePattern = 'meetings-jem';
    protected $baseRouteName = 'admin_app_nationalevent_nationalevent_jem';

    protected function getAllowedEventTypes(): ?array
    {
        return [NationalEventTypeEnum::JEM];
    }

    protected function getForbiddenEventTypes(): ?array
    {
        return null;
    }
}
