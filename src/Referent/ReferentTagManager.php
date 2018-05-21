<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
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

    public function isUpdateNeeded(Adherent $adherent): bool
    {
        $currentTags = array_map(
            function (ReferentTag $tag) {
                return $tag->getCode();
            },
            $adherent->getReferentTags()->toArray()
        );
        $currentTags = array_values($currentTags);
        sort($currentTags);

        $newTags = ManagedAreaUtils::getLocalCodes($adherent);
        $newTags = array_values($newTags);
        sort($newTags);

        return $currentTags !== $newTags;
    }
}
