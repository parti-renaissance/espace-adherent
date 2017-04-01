<?php

namespace AppBundle\Repository;

use AppBundle\Entity\SocialShareCategory;
use Doctrine\ORM\EntityRepository;

class SocialShareCategoryRepository extends EntityRepository
{
    /**
     * @return SocialShareCategory[]
     */
    public function findForWall(): array
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}
