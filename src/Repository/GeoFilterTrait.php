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

        $this->applyGeoFilter($qb, $referent->getManagedArea()->getTags()->toArray(), $alias);
    }

    /**
     * @param ReferentTag[] $referentTags
     */
    public function applyGeoFilter(QueryBuilder $qb, array $referentTags, string $alias): void
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $code = $tag->getCode();

            if (is_numeric($code) || $tag->isDistrictTag()) {
                if ($tag->isDistrictTag()) {
                    $code = substr($code, 6, 2);
                }

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
            } elseif (ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $code) {
                $codesFilter->add("$alias.postAddress.country != 'FR'");
            }
        }

        $qb->andWhere($codesFilter);
    }
}
