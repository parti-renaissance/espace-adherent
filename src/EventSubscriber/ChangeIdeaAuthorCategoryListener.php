<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentTagEnum;
use AppBundle\Repository\IdeasWorkshop\IdeaRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ChangeIdeaAuthorCategoryListener
{
    private $ideaRepository;

    public function __construct(IdeaRepository $ideaRepository)
    {
        $this->ideaRepository = $ideaRepository;
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $adherent = $args->getObject();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $this->needChangeIdeaAuthorCategory =
            $this->hasLaREMTag($adherent->getTags()->getDeleteDiff())
            || $this->hasLaREMTag($adherent->getTags()->getInsertDiff())
            || (
                $args->hasChangedField('mandates')
                && (
                    empty($args->getOldValue('mandates'))
                    || empty($args->getNewValue('mandates'))
                )
            );
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $adherent = $args->getObject();

        if (!$adherent instanceof Adherent || false === $this->needChangeIdeaAuthorCategory) {
            return;
        }

        $this->ideaRepository->updateAuthorCategoryForIdeasOf($adherent);
    }

    private function hasLaREMTag(array $tags): bool
    {
        foreach ($tags as $tag) {
            if (AdherentTagEnum::LAREM === $tag->getName()) {
                return true;
            }
        }

        return false;
    }
}
