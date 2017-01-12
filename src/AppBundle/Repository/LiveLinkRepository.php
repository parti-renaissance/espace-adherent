<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LiveLink;
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
