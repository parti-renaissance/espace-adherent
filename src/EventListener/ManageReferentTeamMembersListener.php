<?php

namespace App\EventListener;

use App\Entity\Adherent;
use App\Entity\MunicipalManagerSupervisorRole;
use App\Entity\Referent;
use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use App\Entity\ReferentTeamMember;
use App\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
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

    private $relatedPersonLinks = [];

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
                if (
                    $adherent->isCoReferent()
                    || $adherent->isJecouteManager()
                    || $adherent->isMunicipalManagerSupervisor()
                    || $personLink->isCoReferent()
                    || $personLink->isJecouteManager()
                    || $personLink->isMunicipalManagerSupervisor()
                ) {
                    if ($adherent->isCoReferent()) {
                        if (!$personLink->isCoReferent()) {
                            $teamMember = $adherent->getReferentTeamMember();
                            $personLink->setCoReferent($adherent->isLimitedCoReferent() ? ReferentPersonLink::LIMITED_CO_REFERENT : ReferentPersonLink::CO_REFERENT);
                            $personLink->setRestrictedCommittees($teamMember->getRestrictedCommittees()->toArray());
                            $personLink->setRestrictedCities($teamMember->getRestrictedCities());

                            $uow->recomputeSingleEntityChangeSet($this->manager->getClassMetadata(ReferentPersonLink::class), $personLink);
                        } else {
                            $this->updateReferentTeamMemberFromPersonLink($teamMember = $adherent->getReferentTeamMember(), $personLink);
                            array_map(function (ReferentPersonLink $otherPersonLink) use ($personLink, $teamMember) {
                                if ($personLink === $otherPersonLink) {
                                    return;
                                }
                                $otherPersonLink->setCoReferent($personLink->getCoReferent());
                                $otherPersonLink->setRestrictedCommittees($personLink->getRestrictedCommittees());
                                $otherPersonLink->setRestrictedCities($personLink->getRestrictedCities());

                                $this->relatedPersonLinks[] = $otherPersonLink->getId();
                            }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));
                            $uow->computeChangeSets();
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

                    if ($adherent->isMunicipalManagerSupervisor()) {
                        if (!$personLink->isMunicipalManagerSupervisor()) {
                            $personLink->setIsMunicipalManagerSupervisor(true);
                            $uow->recomputeSingleEntityChangeSet($this->manager->getClassMetadata(ReferentPersonLink::class), $personLink);
                        }
                    } elseif ($personLink->isMunicipalManagerSupervisor()) {
                        $this->initializeMunicipalManagerSupervisorNewRole($personLink, $currentReferent, $adherent);
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
                && (
                    $adherent->isCoReferent()
                    || $adherent->isJecouteManager()
                    || $adherent->isMunicipalManagerSupervisor()
                )
            ) {
                $this->removeRoles($adherent, $personLink);
                $uow->computeChangeSets();
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $personLink) {
            if ($personLink instanceof ReferentPersonLink && !\in_array($personLink->getId(), $this->relatedPersonLinks)) {
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
                    } elseif ($adherent && $adherent->isCoReferent()) {
                        $this->updateReferentTeamMemberFromPersonLink($adherent->getReferentTeamMember(), $personLink);
                        array_map(static function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }
                            $otherPersonLink->setCoReferent($personLink->getCoReferent());
                            $otherPersonLink->setRestrictedCities($personLink->getRestrictedCities());
                            $otherPersonLink->setRestrictedCommittees($personLink->getRestrictedCommittees());
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));
                    }
                } else {
                    if ($adherent && $adherent->isCoReferent()) {
                        array_map(static function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }
                            $otherPersonLink->setCoReferent(null);
                            $otherPersonLink->setRestrictedCities(null);
                            $otherPersonLink->setRestrictedCommittees(null);
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

                // Municipal Manager Supervisor
                if ($personLink->isMunicipalManagerSupervisor()) {
                    if ($adherent && !$adherent->isMunicipalManagerSupervisor()) {
                        $this->initializeMunicipalManagerSupervisorNewRole($personLink, $currentReferent, $adherent);
                    }
                } else {
                    if ($adherent && $adherent->isMunicipalManagerSupervisor()) {
                        array_map(static function (ReferentPersonLink $otherPersonLink) use ($personLink) {
                            if ($personLink === $otherPersonLink) {
                                return;
                            }
                            $otherPersonLink->setIsMunicipalManagerSupervisor(false);
                        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));

                        $this->manager->remove($adherent->getMunicipalManagerSupervisorRole());
                        $adherent->revokeMunicipalManagerSupervisorRole();
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

    private function removeMunicipalManagerSupervisorRoleIfNotUsed(Adherent $adherent, Referent $referent): void
    {
        if (1 === $this->repository->count(['adherent' => $adherent, 'referent' => $referent])) {
            $this->manager->remove($adherent->getMunicipalManagerSupervisorRole());
            $adherent->revokeMunicipalManagerSupervisorRole();
        }
    }

    private function initializeCoReferentNewRole(
        ReferentPersonLink $personLink,
        Adherent $currentReferent,
        Adherent $adherent
    ): void {
        $adherent->setReferentTeamMember($member = new ReferentTeamMember(
            $currentReferent,
            $personLink->isLimitedCoReferent(),
            $personLink->getRestrictedCommittees(),
            $personLink->getRestrictedCities()
        ));
        $this->manager->persist($member);

        array_map(function (ReferentPersonLink $otherPersonLink) use ($adherent, $personLink) {
            if ($personLink === $otherPersonLink) {
                return;
            }
            $otherPersonLink->setAdherent($adherent);
            $otherPersonLink->setCoReferent($personLink->getCoReferent());
            $otherPersonLink->setRestrictedCommittees($personLink->getRestrictedCommittees());
            $otherPersonLink->setRestrictedCities($personLink->getRestrictedCities());

            $this->relatedPersonLinks[] = $otherPersonLink->getId();
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

        array_map(function (ReferentPersonLink $personLink) use ($adherent) {
            $personLink->setAdherent($adherent);
            $personLink->setIsJecouteManager(true);
            $this->relatedPersonLinks[] = $personLink->getId();
        }, $this->repository->findBy(['email' => $adherent->getEmailAddress(), 'referent' => $personLink->getReferent()]));
    }

    private function initializeMunicipalManagerSupervisorNewRole(
        ReferentPersonLink $personLink,
        Adherent $currentReferent,
        Adherent $adherent
    ): void {
        $adherent->setMunicipalManagerSupervisorRole($role = new MunicipalManagerSupervisorRole($currentReferent));

        array_map(function (ReferentPersonLink $personLink) use ($adherent) {
            $personLink->setAdherent($adherent);
            $personLink->setIsMunicipalManagerSupervisor(true);
            $this->relatedPersonLinks[] = $personLink->getId();
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

        if ($adherent->isMunicipalManagerSupervisor()) {
            $this->removeMunicipalManagerSupervisorRoleIfNotUsed($adherent, $personLink->getReferent());
        }
    }

    private function updateReferentTeamMemberFromPersonLink(
        ReferentTeamMember $member,
        ReferentPersonLink $personLink
    ): void {
        $member->setLimited($personLink->isLimitedCoReferent());
        $member->setRestrictedCommittees($personLink->getRestrictedCommittees());
        $member->setRestrictedCities($personLink->getRestrictedCities());
    }
}
