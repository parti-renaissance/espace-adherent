<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Entity\ReferentTeamMember;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;

class ManageReferentTeamMembersListener implements EventSubscriber
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $manager = $args->getEntityManager();
        $uow = $manager->getUnitOfWork();
        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $personLink) {
            if ($personLink instanceof ReferentPersonLink) {
                $changeSet = $uow->getEntityChangeSet($personLink);

                if (!isset($changeSet['isCoReferent'])) {
                    return;
                }

                if (!$adherent = $personLink->getAdherent()) {
                    return;
                }

                if ($personLink->isCoReferent()) {
                    $adherent->setReferentTeamMember($member = new ReferentTeamMember($this->security->getUser()));
                    $manager->persist($member);
                    $uow->computeChangeSets();
                } else {
                    $manager->remove($adherent->getReferentTeamMember());
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }
}
