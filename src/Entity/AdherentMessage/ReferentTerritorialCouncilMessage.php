<?php

namespace App\Entity\AdherentMessage;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentTerritorialCouncilMessage extends AbstractAdherentMessage
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REFERENT_TERRITORIAL_COUNCIL;
    }
}
