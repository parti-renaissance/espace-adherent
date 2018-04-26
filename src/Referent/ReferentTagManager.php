<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ReferentTaggableEntity;
use AppBundle\Repository\ReferentTagRepository;

class ReferentTagManager
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    public function assignReferentLocalTags(ReferentTaggableEntity $entity): void
    {
        $entity->clearReferentTags();

        if (empty($codes = ManagedAreaUtils::getLocalCodes($entity))) {
            return;
        }

        foreach ($this->referentTagRepository->findByCodes($codes) as $referentTag) {
            $entity->addReferentTag($referentTag);
        }
    }
}
