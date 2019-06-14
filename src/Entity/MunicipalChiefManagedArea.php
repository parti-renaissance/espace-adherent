<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Intl\FranceCitiesBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="municipal_chief_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalChiefManagedArea extends ManagedArea
{
    /** @Assert\IsTrue(message="Au moins une des valeurs saisies dans le champ Chef municipal n'est pas un code INSEE de ville valide.") */
    public function isValidFrenchCodes(): bool
    {
        return empty(array_diff($this->getCodes(), array_keys(FranceCitiesBundle::getCityByInseeCode())));
    }
}
