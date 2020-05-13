<?php

namespace App\Repository;

use App\Entity\BaseGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractGroupRepository extends ServiceEntityRepository
{
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const ONLY_APPROVED = 1;
    public const INCLUDE_UNAPPROVED = 2;

    /**
     * Finds a BaseGroup instance by its unique canonical name.
     */
    public function findOneByName(string $name): ?BaseGroup
    {
        $canonicalName = BaseGroup::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }
}
