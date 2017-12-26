<?php

namespace AppBundle\Repository;

use AppBundle\Entity\BaseGroup;
use Doctrine\ORM\EntityRepository;

class BaseGroupRepository extends EntityRepository
{
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    const ONLY_APPROVED = 1;
    const INCLUDE_UNAPPROVED = 2;

    /**
     * Finds a BaseGroup instance by its unique canonical name.
     *
     * @param string $name
     *
     * @return BaseGroup|null
     */
    public function findOneByName(string $name): ?BaseGroup
    {
        $canonicalName = BaseGroup::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }
}
