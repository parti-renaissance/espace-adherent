<?php

namespace App\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PoliticalCommitteeManager
{
    public const CREATE_ACTION = 'create';
    public const REMOVE_ACTION = 'remove';
    public const ACTIONS = [
        self::CREATE_ACTION,
        self::REMOVE_ACTION,
    ];
    public const MAX_MAYOR_AND_LEADER = 3;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MandateRepository */
    private $mandateRepository;
    /** @var PoliticalCommitteeMembershipRepository */
    private $membershipRepository;
    /** @var TerritorialCouncilAdherentMandateRepository */
    private $tcMandateRepository;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        MandateRepository $mandateRepository,
        PoliticalCommitteeMembershipRepository $membershipRepository,
        TerritorialCouncilAdherentMandateRepository $tcMandateRepository,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->mandateRepository = $mandateRepository;
        $this->membershipRepository = $membershipRepository;
        $this->tcMandateRepository = $tcMandateRepository;
        $this->translator = $translator;
    }

    public function createMembership(
        Adherent $adherent,
        PoliticalCommittee $politicalCommittee,
        string $qualityName
    ): PoliticalCommitteeMembership {
        $pcMembership = new PoliticalCommitteeMembership($politicalCommittee, $adherent);
        $pcQuality = new PoliticalCommitteeQuality($qualityName);
        $pcMembership->addQuality($pcQuality);
        $pcMembership->setIsAdditional($this->checkIsAdditional($qualityName, $adherent));
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        return $pcMembership;
    }

    public function createMembershipFromTerritorialCouncilMembership(TerritorialCouncilMembership $tcMembership): void
    {
        $pcMembership = new PoliticalCommitteeMembership(
            $tcMembership->getTerritorialCouncil()->getPoliticalCommittee(),
            $tcMembership->getAdherent()
        );

        $this->updateOfficioMembersFromTerritorialCouncilMembership($pcMembership, $tcMembership);
    }

    public function updateOfficioMembersFromTerritorialCouncilMembership(
        PoliticalCommitteeMembership $pcMembership,
        TerritorialCouncilMembership $tcMembership
    ): void {
        $qualities = $tcMembership->getQualityNames();

        foreach ($pcMembership->getQualities() as $quality) {
            if (\in_array($name = $quality->getName(), TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)
                && !\in_array($name, $qualities)) {
                $this->entityManager->remove($quality);
            }
        }
        $this->entityManager->flush();

        foreach ($qualities as $name) {
            if (\in_array($name, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)) {
                $pcQuality = new PoliticalCommitteeQuality($name);
                $pcMembership->addQuality($pcQuality);
            }
        }

        if ($pcMembership->getQualities()->count() > 0) {
            $this->entityManager->persist($pcMembership);
        } elseif ($pcMembership->getId()) {
            $this->entityManager->remove($pcMembership);
        }

        $this->entityManager->flush();
    }

    public function addPoliticalCommitteeQuality(Adherent $adherent, string $qualityName): void
    {
        if (!$pcMembership = $adherent->getPoliticalCommitteeMembership()) {
            if (!($tcMembership = $adherent->getTerritorialCouncilMembership())) {
                return;
            }

            $this->createMembership($adherent, $tcMembership->getTerritorialCouncil()->getPoliticalCommittee(), $qualityName);

            return;
        }

        $pcMembership->addQuality(new PoliticalCommitteeQuality($qualityName));
    }

    public function removePoliticalCommitteeQuality(Adherent $adherent, string $qualityName): void
    {
        if ($pcMembership = $adherent->getPoliticalCommitteeMembership()) {
            $this->removeQualityByName($pcMembership, $qualityName);
        }
    }

    public function handleTerritorialCouncilMembershipUpdate(
        Adherent $adherent,
        ?TerritorialCouncilMembership $oldTcMembership = null
    ): void {
        $tcMembership = $adherent->getTerritorialCouncilMembership();
        $pcMembership = $adherent->getPoliticalCommitteeMembership();

        if (null === $oldTcMembership) {
            if (null === $tcMembership) {
                if (null === $pcMembership) {
                    return;
                }

                // if no TerritorialCouncil membership, but PoliticalCommittee membership is present, we should remove it
                $adherent->revokePoliticalCommitteeMembership();
                $this->entityManager->flush();

                return;
            }

            if (null === $pcMembership) {
                // create Political committee membership
                $this->createMembershipFromTerritorialCouncilMembership($tcMembership);

                return;
            }

            // update Political committee membership
            $this->updateManagedInAdminQualitiesInMembership($adherent, $tcMembership, $pcMembership);
        }

        if (!$tcMembership) {
            $adherent->revokePoliticalCommitteeMembership();
            $this->entityManager->flush();

            return;
        }

        // do nothing if no quality modifications
        if (\array_values($tcMembership->getQualityNames()) == \array_values($oldTcMembership->getQualityNames())) {
            return;
        }

        // we create a PoliticalCommittee membership basing on TerritorialCouncil membership
        if (null === $pcMembership) {
            $this->createMembershipFromTerritorialCouncilMembership($tcMembership);

            return;
        }

        $this->updateManagedInAdminQualitiesInMembership($adherent, $tcMembership, $pcMembership);
    }

    public function createMayorOrLeaderMembership(TerritorialCouncil $territorialCouncil, Adherent $adherent): void
    {
        $this->checkTerritorialCouncil($adherent, $territorialCouncil);

        if ($adherent->hasPoliticalCommitteeMembership()) {
            $this->throwException(
                'political_committee.membership.adherent_has_already',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ politicalCommittee }}' => $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee()->getName(),
                ]
            );
        }

        $politicalCommittee = $territorialCouncil->getPoliticalCommittee();
        $nbMembers = $this->membershipRepository->countLeaderAndMayorMembersFor($politicalCommittee);
        if ($nbMembers >= self::MAX_MAYOR_AND_LEADER) {
            $this->throwException(
                'political_committee.membership.has_max_number_of_mayor_and_leader',
                [
                    '{{ max }}' => self::MAX_MAYOR_AND_LEADER,
                    '{{ politicalCommittee }}' => $territorialCouncil->getPoliticalCommittee()->getName(),
                ]
            );
        }

        $isMayor = $this->mandateRepository->hasMayorMandate($adherent);
        $quality = TerritorialCouncilQualityEnum::LEADER;
        if ($isMayor) {
            $quality = TerritorialCouncilQualityEnum::MAYOR;
        }

        $membership = $this->createMembership($adherent, $politicalCommittee, $quality);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();
    }

    public function canAddQuality(string $qualityName, Adherent $adherent): bool
    {
        if (\in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)
            || (\in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS)
                && ($tcMembership = $adherent->getTerritorialCouncilMembership())
                && $this->tcMandateRepository->findActiveMandateWithQuality($adherent, $tcMembership->getTerritorialCouncil(), $qualityName))) {
            return true;
        }

        return false;
    }

    public function removeMayorOrLeaderMembership(TerritorialCouncil $territorialCouncil, Adherent $adherent): void
    {
        $this->checkTerritorialCouncil($adherent, $territorialCouncil);

        if (!$adherent->hasPoliticalCommitteeMembership()) {
            $this->throwException(
                'political_committee.membership.adherent_has_no_membership',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ territorialCouncil }}' => $territorialCouncil->getNameCodes(),
                ]
            );
        }

        $isMayor = $this->mandateRepository->hasMayorMandate($adherent);
        $quality = TerritorialCouncilQualityEnum::LEADER;
        if ($isMayor) {
            $quality = TerritorialCouncilQualityEnum::MAYOR;
        }
        $politicalCommitteeMembership = $adherent->getPoliticalCommitteeMembership();
        $this->removeQualityByName($politicalCommitteeMembership, $quality);

        $this->entityManager->flush();
    }

    private function checkIsAdditional(string $qualityName, Adherent $adherent): bool
    {
        if (!\in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS)
            || !($tcMembership = $adherent->getTerritorialCouncilMembership())) {
            return false;
        }

        $mandate = $this->tcMandateRepository->findActiveMandateWithQuality($adherent, $tcMembership->getTerritorialCouncil(), $qualityName);

        return $mandate->isAdditionallyElected();
    }

    private function updateManagedInAdminQualitiesInMembership(
        Adherent $adherent,
        TerritorialCouncilMembership $tcMembership,
        PoliticalCommitteeMembership $pcMembership
    ): void {
        if ($tcMembership->getTerritorialCouncil()->getId() !== $pcMembership->getPoliticalCommittee()->getTerritorialCouncil()->getId()) {
            // if TerritorialCouncil is different in TerritorialCouncil and PoliticalCommittee memberships
            $adherent->revokePoliticalCommitteeMembership();
            $this->entityManager->flush();
            $this->createMembershipFromTerritorialCouncilMembership($tcMembership);

            return;
        }
        $tcQualities = $tcMembership->getManagedInAdminQualityNames();
        $pcQualities = $pcMembership->getManagedInAdminQualityNames();

        if (\array_values($pcQualities) == \array_values($tcQualities)) {
            return;
        }

        foreach (TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_MANAGED_IN_ADMIN_MEMBERS as $quality) {
            if (\in_array($quality, $tcQualities) && !\in_array($quality, $pcQualities)) {
                $pcMembership->addQuality(new PoliticalCommitteeQuality($quality));
            }

            if (!\in_array($quality, $tcQualities) && \in_array($quality, $pcQualities)) {
                $this->removeQualityByName($pcMembership, $quality);
            }
        }

        $this->entityManager->flush();
    }

    private function removeQualityByName(PoliticalCommitteeMembership $pcMembership, string $qualityName): void
    {
        $pcMembership->removeQualityWithName($qualityName);
        if (0 === $pcMembership->getQualities()->count()) {
            $this->entityManager->remove($pcMembership);
        }
    }

    private function checkTerritorialCouncil(Adherent $adherent, TerritorialCouncil $territorialCouncil): void
    {
        if (!$adherent->hasTerritorialCouncilMembership()
            || $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getId() !== $territorialCouncil->getId()) {
            $this->throwException(
                'territorial_council.adherent_has_no_membership',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ territorialCouncil }}' => $territorialCouncil->getNameCodes(),
                ]
            );
        }
    }

    private function throwException(string $msgId, array $variables): void
    {
        throw new PoliticalCommitteeMembershipException($this->translator->trans($msgId, $variables));
    }
}
