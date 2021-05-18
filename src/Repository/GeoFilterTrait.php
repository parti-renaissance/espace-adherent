<?php

namespace App\Repository;

use App\Entity\ReferentTag;
use App\Utils\AreaUtils;
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
            } elseif (AreaUtils::CODE_NOUVEAU_RHONE === $code) {
                // all zones of the department 69, except of the metropolis of Lyon
                $inseeCodeExpression = $qb->expr()->andX();
                foreach (AreaUtils::METROPOLIS[AreaUtils::CODE_METROPOLIS_LYON] as $key => $inseeCode) {
                    $inseeCodeExpression->add("$alias.postAddress.city NOT LIKE :inseeCode_69_$code$key");
                    $qb->setParameter("inseeCode_69_$code$key", "%-$inseeCode");
                }

                $codesFilter->add(
                    $qb->expr()->andX(
                        "$countryColumn = 'FR'",
                        $qb->expr()->like("$postalCodeColumn", ':dpt_69'),
                        $inseeCodeExpression
                    )
                );
                $qb->setParameter('dpt_69', '69%');
            } elseif ($tag->isMetropolisTag() && \array_key_exists($code, AreaUtils::METROPOLIS)) {
                $inseeCodeExpression = $qb->expr()->orX();
                foreach (AreaUtils::METROPOLIS[$code] as $key => $inseeCode) {
                    $inseeCodeExpression->add("$alias.postAddress.city LIKE :inseeCode_$code$key");
                    $qb->setParameter("inseeCode_$code$key", "%-$inseeCode");
                }

                $codesFilter->add(
                    $qb->expr()->andX(
                        "$countryColumn = 'FR'",
                        $inseeCodeExpression
                    )
                );
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
            } elseif (2 === mb_strlen($code)) {
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
