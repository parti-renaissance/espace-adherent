<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Entity\ReferentTeamMember;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;

class ManageReferentTeamMembersListener implements EventSubscriber
{
    private $security;
    /** @var ObjectManager */
    private $manager;
    /** @var ReferentPersonLinkRepository */
    private $repository;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $this->manager = $args->getEntityManager();
        $uow = $this->manager->getUnitOfWork();
        $currentReferent = $this->security->getUser();

        if (!$currentReferent instanceof Adherent) {
            return;
        }

        $this->repository = $this->manager->getRepository(ReferentPersonLink::class);

        foreach ($uow->getScheduledEntityInsertions() as $personLink) {
            if (($personLink instanceof ReferentPersonLink) && $adherent = $personLink->getAdherent()) {
                if ($adherent->isCoReferent()) {
                    if (!$personLink->isCoReferent()) {
                        $personLink->setIsCoReferent(true);
                        $uow->recomputeSingleEntityChangeSet($this->manager->getClassMetadata(ReferentPersonLink::class), $personLink);
                    }
                } elseif ($personLink->isCoReferent()) {
                    $adherent->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
                    $this->manager->persist($member);

                    array_map(function (ReferentPersonLink $personLink) use ($adherent) {
                        $personLink->setAdherent($adherent);
                        $personLink->setIsCoReferent(true);
                    }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

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
                && $adherent->isCoReferent()
            ) {
                $this->removeCoReferentRoleIfNotUsed($adherent, $personLink->getReferent());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $personLink) {
            if ($personLink instanceof ReferentPersonLink) {
                $changeSet = $uow->getEntityChangeSet($personLink);

                if (isset($changeSet['adherent'])) {
                    $adherent = $changeSet['adherent'][0];
                    if ($adherent instanceof Adherent && $adherent->isCoReferent()) {
                        $this->removeCoReferentRoleIfNotUsed($adherent, $personLink->getReferent());
                    }
                }

                if ($personLink->isCoReferent()) {
                    if (($adherent = $personLink->getAdherent()) && !$adherent->isCoReferent()) {
                        $adherent->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
                        $this->manager->persist($member);

                        array_map(function (ReferentPersonLink $personLink) use ($adherent) {
                            $personLink->setAdherent($adherent);
                            $personLink->setIsCoReferent(true);
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

                        $uow->computeChangeSets();
                    }
                } else {
                    if (($adherent = $personLink->getAdherent()) && $adherent->isCoReferent()) {
                        array_map(function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }

                            $otherPersonLink->setAdherent(null);
                            $otherPersonLink->setIsCoReferent(false);
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

                        $this->manager->remove($adherent->getReferentTeamMember());
                        $uow->computeChangeSets();
                    }

                    $personLink->setAdherent(null);
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

    private function removeCoReferentRoleIfNotUsed(Adherent $adherent, Referent $referent): void
    {
        if (1 === $this->repository->count(['adherent' => $adherent, 'referent' => $referent])) {
            $this->manager->remove($adherent->getReferentTeamMember());
        }
    }
}
