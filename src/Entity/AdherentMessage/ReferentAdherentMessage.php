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
class ReferentAdherentMessage extends AbstractAdherentMessage
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REFERENT;
    }
}
