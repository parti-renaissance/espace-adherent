<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;

class TerritorialCouncilActiveMandateHandler extends AbstractTerritorialCouncilHandler
{
    /** @var TerritorialCouncilAdherentMandate[]|array */
    private $mandates = [];

    public function supports(Adherent $adherent): bool
    {
        $this->mandates = $adherent->findTerritorialCouncilMandates(null, true);

        return \count($this->mandates) > 0;
    }

    public function handle(Adherent $adherent): void
    {
        $tcMembership = $adherent->getTerritorialCouncilMembership();
        $pcMembership = $adherent->getPoliticalCommitteeMembership();

        foreach ($this->mandates as $mandate) {
            $tcFromMandate = $mandate->getTerritorialCouncil();
            $qualityNameFromMandate = $mandate->getQuality();

            // if no TC/PC membership or it's not the same, we create a new one
            if (null === $tcMembership || $tcMembership->getTerritorialCouncil() !== $tcFromMandate) {
                if ($tcMembership && $tc = $tcMembership->getTerritorialCouncil()) {
                    $this->removeMembership($adherent, $tc);
                }

                $quality = new TerritorialCouncilQuality($qualityNameFromMandate, '');
                $this->addMembership($adherent, $tcFromMandate, $quality);

                return;
            }

            // if TC membership does not have this quality, we add it
            if (!\in_array($qualityNameFromMandate, $tcMembership->getQualityNames())) {
                $quality = new TerritorialCouncilQuality($qualityNameFromMandate, '');
                $tcMembership->addQuality($quality);

                // if PC is not for the same TC, we remove PC and create a new one corresponding to TC
                if ($pcMembership && $tcMembership->getTerritorialCouncil()->getId() !== $pcMembership->getPoliticalCommittee()->getTerritorialCouncil()->getId()) {
                    $adherent->revokePoliticalCommitteeMembership();
                    $this->em->flush();
                }

                $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityNameFromMandate);
                $this->em->flush();
            }
        }
    }

    public function getPriority(): int
    {
        return -1;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return [];
    }

    protected function getQualityName(): string
    {
        return '';
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return '';
    }
}
