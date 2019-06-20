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
                if ($adherent->isCoReferent() || $adherent->isJecouteManager() || $personLink->isCoReferent() ||
                    $personLink->isJecouteManager()) {
                    if ($adherent->isCoReferent()) {
                        if (!$personLink->isCoReferent()) {
                            $personLink->setIsCoReferent(true);
                            $uow->recomputeSingleEntityChangeSet($this->manager->getClassMetadata(ReferentPersonLink::class), $personLink);
                        }
                    } elseif ($personLink->isCoReferent()) {
                        $this->initializeCoReferentNewRole($personLink, $currentReferent, $adherent);
                        $uow->computeChangeSets();
                    }

                    if ($adherent->isJecouteManager()) {
                        if (!$personLink->isJecouteManager()) {
                            $personLink->setIsJecouteManager(true);
                            $uow->recomputeSingleEntityChangeSet($this->manager->getClassMetadata(ReferentPersonLink::class), $personLink);
                        }
                    } elseif ($personLink->isJecouteManager()) {
                        $this->initializeJecouteManagerNewRole($personLink, $currentReferent, $adherent);
                        $uow->computeChangeSets();
                    }
                } else {
                    $personLink->detachAdherent();
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $personLink) {
            if (
                $personLink instanceof ReferentPersonLink
                && ($adherent = $personLink->getAdherent())
                && ($adherent->isCoReferent() || $adherent->isJecouteManager())
            ) {
                $this->removeRoles($adherent, $personLink);
                $uow->computeChangeSets();
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $personLink) {
            if ($personLink instanceof ReferentPersonLink) {
                $changeSet = $uow->getEntityChangeSet($personLink);

                if (isset($changeSet['adherent'])) {
                    $adherent = $changeSet['adherent'][0];
                    if ($adherent instanceof Adherent) {
                        $this->removeRoles($adherent, $personLink);
                    }
                }

                $adherent = $personLink->getAdherent();

                // Co-Referent
                if ($personLink->isCoReferent()) {
                    if ($adherent && !$adherent->isCoReferent()) {
                        $this->initializeCoReferentNewRole($personLink, $currentReferent, $adherent);
                    }
                } else {
                    if ($adherent && $adherent->isCoReferent()) {
                        array_map(static function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }
                            $otherPersonLink->setIsCoReferent(false);
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

                        $this->manager->remove($adherent->getReferentTeamMember());
                    }
                }

                // J'écoute
                if ($personLink->isJecouteManager()) {
                    if ($adherent && !$adherent->isJecouteManager()) {
                        $this->initializeJecouteManagerNewRole($personLink, $currentReferent, $adherent);
                    }
                } else {
                    if ($adherent && $adherent->isJecouteManager()) {
                        array_map(static function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }
                            $otherPersonLink->setIsJecouteManager(false);
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

                        $this->manager->remove($adherent->getJecouteManagedArea());
                        $adherent->revokeJecouteManager();
                    }
                }

                $uow->computeChangeSets();
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

    private function removeJecouteManagerRoleIfNotUsed(Adherent $adherent, Referent $referent): void
    {
        if (1 === $this->repository->count(['adherent' => $adherent, 'referent' => $referent])) {
            $this->manager->remove($adherent->getJecouteManagedArea());
            $adherent->revokeJecouteManager();
        }
    }

    private function initializeCoReferentNewRole(
        ReferentPersonLink $personLink,
        Adherent $currentReferent,
        Adherent $adherent
    ): void {
        $adherent->setReferentTeamMember($member = new ReferentTeamMember($currentReferent));
        $this->manager->persist($member);

        array_map(static function (ReferentPersonLink $personLink) use ($adherent) {
            $personLink->setAdherent($adherent);
            $personLink->setIsCoReferent(true);
        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));
    }

    private function initializeJecouteManagerNewRole(
        ReferentPersonLink $personLink,
        Adherent $currentReferent,
        Adherent $adherent
    ): void {
        $adherent->setJecouteManagedAreaCodesAsString(
            implode(',', $currentReferent->getManagedAreaTagCodes())
        );

        array_map(static function (ReferentPersonLink $personLink) use ($adherent) {
            $personLink->setAdherent($adherent);
            $personLink->setIsJecouteManager(true);
        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));
    }

    private function removeRoles(Adherent $adherent, ReferentPersonLink $personLink): void
    {
        if ($adherent->isCoReferent()) {
            $this->removeCoReferentRoleIfNotUsed($adherent, $personLink->getReferent());
        }

        if ($adherent->isJecouteManager()) {
            $this->removeJecouteManagerRoleIfNotUsed($adherent, $personLink->getReferent());
        }
    }
}
