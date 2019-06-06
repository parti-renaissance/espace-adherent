<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="municipal_chief_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalChiefManagedArea extends ManagedArea
{
    public function setCodes(array $codes): void
    {
        $codes = array_filter($codes, function ($value) {
            return \in_array($value, array_keys(FranceCitiesBundle::getCityByInseeCode()));
        });

        parent::setCodes($codes);
    }
}
