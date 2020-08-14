<?php

namespace App\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\ORM\EntityManagerInterface;

class PoliticalCommitteeManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createMembership(
        Adherent $adherent,
        PoliticalCommittee $politicalCommittee,
        string $qualityName
    ): PoliticalCommitteeMembership {
        $pcMembership = new PoliticalCommitteeMembership(
            $politicalCommittee,
            $adherent
        );
        $pcQuality = new PoliticalCommitteeQuality($qualityName);
        $pcMembership->addQuality($pcQuality);

        return $pcMembership;
    }

    public function createMembershipFromTerritorialCouncilMembership(TerritorialCouncilMembership $tcMembership): void
    {
        $pcMembership = new PoliticalCommitteeMembership(
            $tcMembership->getTerritorialCouncil()->getPoliticalCommittee(),
            $tcMembership->getAdherent()
        );

        $qualities = \array_map(function (TerritorialCouncilQuality $quality) {
            return $quality->getName();
        }, $tcMembership->getQualities()->toArray());

        foreach ($qualities as $name) {
            if (\in_array($name, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)) {
                $pcQuality = new PoliticalCommitteeQuality($name);
                $pcMembership->addQuality($pcQuality);
            }
        }

        if ($pcMembership->getQualities()->count() > 0) {
            $this->entityManager->persist($pcMembership);
            $this->entityManager->flush();
        }
    }

    public function addPoliticalCommitteeQuality(
        Adherent $adherent,
        string $qualityName,
        bool $checkOfficioQuality = false
    ): void {
        if ($pcMembership = $adherent->getPoliticalCommitteeMembership()) {
            if (!$checkOfficioQuality
                || ($checkOfficioQuality && \in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS))) {
                $pcMembership->addQuality(new PoliticalCommitteeQuality($qualityName));
            }
        }
    }

    public function removePoliticalCommitteeQuality(Adherent $adherent, string $qualityName): void
    {
        if ($pcMembership = $adherent->getPoliticalCommitteeMembership()) {
            $pcMembership->removeQualityWithName($qualityName);
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
                $pcMembership->removeQualityWithName($quality);
            }
        }

        $this->entityManager->flush();
    }
}
