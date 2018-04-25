<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ReferentTagRepository extends EntityRepository
{
    public function findByCodes(array $codes): array
    {
        return $this->findBy(['code' => $codes]);
    }
}
