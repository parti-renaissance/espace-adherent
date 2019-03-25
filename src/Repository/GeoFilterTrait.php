<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\QueryBuilder;

trait GeoFilterTrait
{
    private function applyReferentGeoFilter(QueryBuilder $qb, Adherent $referent, string $alias): void
    {
        if (!$referent->isReferent()) {
            return;
        }

        $codes = array_map(function (ReferentTag $tag) {
            return $tag->getCode();
        }, $referent->getAdherentReferentData()->getTags()->toArray());

        $this->applyGeoFilter($qb, $codes, $alias);
    }

    private function applyGeoFilter(QueryBuilder $qb, array $codes, string $alias): void
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($codes as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        $alias.'.postAddress.country = \'FR\'',
                        $qb->expr()->like("$alias.postAddress.postalCode", ":code_$key")
                    )
                );

                $qb->setParameter("code_$key", "$code%");
            } elseif (2 === \mb_strlen($code)) {
                // Country
                $codesFilter->add($qb->expr()->eq("$alias.postAddress.country", ":code_$key"));
                $qb->setParameter("code_$key", $code);
            }
        }

        $qb->andWhere($codesFilter);
    }
}
