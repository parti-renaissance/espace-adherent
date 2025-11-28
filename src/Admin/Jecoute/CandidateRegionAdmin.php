<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Entity\Geo\Zone;

class CandidateRegionAdmin extends AbstractRegionAdmin
{
    protected $baseRoutePattern = 'jecoute-candidate-region';
    protected $baseRouteName = 'jecoute_candidate_region';

    protected function getZoneTypes(): array
    {
        return [Zone::REGION];
    }
}
