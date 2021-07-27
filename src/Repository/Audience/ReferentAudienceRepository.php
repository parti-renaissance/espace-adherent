<?php

namespace App\Repository\Audience;

use App\Entity\Audience\ReferentAudience;
use Doctrine\Persistence\ManagerRegistry;

class ReferentAudienceRepository extends AbstractAudienceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentAudience::class);
    }
}
