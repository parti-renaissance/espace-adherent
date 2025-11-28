<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Entity\Geo\Zone;

class ReferentRegionAdmin extends AbstractRegionAdmin
{
    protected $baseRoutePattern = 'jecoute-referent-region';
    protected $baseRouteName = 'jecoute_referent_region';

    protected function getZoneTypes(): array
    {
        return [Zone::DEPARTMENT, Zone::BOROUGH];
    }
}
