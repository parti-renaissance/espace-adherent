<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Adherent;
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
        $currentReferent = $this->security->getUser();

        if (!$currentReferent instanceof Adherent) {
            return;
        }

        foreach ($uow->getScheduledEntityInsertions() as $personLink) {
            if ($personLink instanceof ReferentPersonLink) {
                if ($personLink->isCoReferent()) {
                    $personLink->getAdherent()->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
                    $manager->persist($member);
                    $uow->computeChangeSets();
                } else {
                    $personLink->setAdherent(null);
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $personLink) {
            if (
                $personLink instanceof ReferentPersonLink
                && ($adherent = $personLink->getAdherent())
                && $member = $adherent->getReferentTeamMember()
            ) {
                $manager->remove($member);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $personLink) {
            if ($personLink instanceof ReferentPersonLink) {
                $changeSet = $uow->getEntityChangeSet($personLink);

                if (isset($changeSet['adherent'])) {
                    $adherent = $changeSet['adherent'][0];
                    if ($adherent instanceof Adherent && $member = $adherent->getReferentTeamMember()) {
                        $manager->remove($member);
                    }

                    $adherent = $changeSet['adherent'][1];
                    if (($adherent instanceof Adherent) && !isset($changeSet['isCoReferent']) && $personLink->isCoReferent()) {
                        $adherent->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
                        $manager->persist($member);
                        $uow->computeChangeSets();
                    }
                }

                if (isset($changeSet['isCoReferent'])) {
                    if ($personLink->isCoReferent()) {
                        $personLink->getAdherent()->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
                        $manager->persist($member);
                        $uow->computeChangeSets();
                    } else {
                        if ($member = $personLink->getAdherent()->getReferentTeamMember()) {
                            $manager->remove($member);
                        }
                        $personLink->setAdherent(null);
                    }
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
