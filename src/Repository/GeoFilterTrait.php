<?php

namespace App\Repository;

use App\Entity\ReferentTag;
use Doctrine\ORM\QueryBuilder;

trait GeoFilterTrait
{
    /**
     * @param ReferentTag[] $referentTags
     */
    public function applyGeoFilter(
        QueryBuilder $qb,
        array $referentTags,
        string $alias,
        string $countryColumn = null,
        string $postalCodeColumn = null,
        string $referentTagsColumn = null
    ): void {
        if (!$countryColumn) {
            $countryColumn = "$alias.postAddress.country";
        }

        if (!$postalCodeColumn) {
            $postalCodeColumn = "$alias.postAddress.postalCode";
        }

        $codesFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $code = $tag->getCode();

            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        "${countryColumn} = 'FR'",
                        $qb->expr()->like("${postalCodeColumn}", ":code_$key")
                    )
                );

                $qb->setParameter("code_$key", "$code%");
            } elseif ($tag->isDistrictTag()) {
                if (!$referentTagsColumn) {
                    $code = substr($code, 6, 2);

                    // Postal code prefix
                    $codesFilter->add(
                        $qb->expr()->andX(
                            "${countryColumn} = 'FR'",
                            $qb->expr()->like("${postalCodeColumn}", ":code_$key")
                        )
                    );

                    $qb->setParameter("code_$key", "$code%");

                    continue;
                }

                $codesFilter->add("$referentTagsColumn = :tag_$key");
                $qb->setParameter("tag_$key", $tag);
            } elseif (2 === \mb_strlen($code)) {
                // Country
                $codesFilter->add($qb->expr()->eq("${countryColumn}", ":code_$key"));
                $qb->setParameter("code_$key", $code);
            } elseif (ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $code) {
                $codesFilter->add("${countryColumn} != 'FR'");
            }
        }

        $qb->andWhere($codesFilter);
    }
}
