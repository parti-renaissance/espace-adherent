<?php

namespace AppBundle\Repository;

use AppBundle\Entity\HomeBlock;
use Doctrine\ORM\EntityRepository;

class HomeBlockRepository extends EntityRepository
{
    /**
     * @return HomeBlock[]
     */
    public function findHomeBlocks()
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}
