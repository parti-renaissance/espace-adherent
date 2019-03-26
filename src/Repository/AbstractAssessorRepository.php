<?php

namespace AppBundle\Repository;

use AppBundle\Entity\AssessorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractAssessorRepository extends ServiceEntityRepository
{
    protected static function addAndWherePostalCodeFindInSet(
        QueryBuilder $qb,
        AssessorRequest $assessorRequest,
        string $alias
    ): QueryBuilder {
        return $qb
            ->andWhere("FIND_IN_SET(:postalCode, $alias.postalCode) > 0")
            ->setParameter('postalCode', $assessorRequest->getAssessorPostalCode())
        ;
    }
}
