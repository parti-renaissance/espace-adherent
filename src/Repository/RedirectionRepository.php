<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Redirection;
use Doctrine\ORM\EntityRepository;

class RedirectionRepository extends EntityRepository
{
    public function findOneByOriginUri(string $url): ?Redirection
    {
        return $this->findOneBy(['from' => $url]);
    }
}
