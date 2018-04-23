<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\EntityRepository;

class ReferentTagRepository extends EntityRepository
{
    public function findOneByCode(string $code): ?ReferentTag
    {
        return $this->findOneBy(['code' => $code]);
    }
}
