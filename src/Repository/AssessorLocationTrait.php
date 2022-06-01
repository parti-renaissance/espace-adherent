<?php

namespace App\Repository;

use App\Entity\AssessorRequest;
use Doctrine\ORM\QueryBuilder;

trait AssessorLocationTrait
{
    protected static function addAndWhereAssessorRequestLocation(
        QueryBuilder $qb,
        AssessorRequest $assessorRequest,
        string $alias
    ): QueryBuilder {
        if ($assessorRequest->isFrenchAssessorRequest()) {
            return $qb
                ->andWhere("FIND_IN_SET(:postalCode, $alias.postalCode) > 0")
                ->setParameter('postalCode', $assessorRequest->getAssessorPostalCode())
                ->andWhere('vp.city = :city')
                ->setParameter('city', $assessorRequest->getAssessorCity())
            ;
        }

        return $qb
            ->andWhere("$alias.country = :countryCode")
            ->setParameter('countryCode', $assessorRequest->getAssessorCountry())
        ;
    }
}
