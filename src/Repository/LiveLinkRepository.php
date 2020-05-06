<?php

namespace App\Repository;

use App\Entity\LiveLink;
use Doctrine\ORM\EntityRepository;

class LiveLinkRepository extends EntityRepository
{
    /**
     * @return LiveLink[]
     */
    public function findHomeLiveLinks()
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}
