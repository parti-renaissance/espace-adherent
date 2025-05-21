<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\AgoraMembership;
use App\Entity\Geo\Zone;
use App\Repository\CommitteeRepository;
use App\Repository\VotingPlatform\VoterRepository;

class AdherentInstances
{
    public function __construct(
        private readonly CommitteeRepository $committeeRepository,
        private readonly VoterRepository $voterRepository,
    ) {
    }

    public function generate(Adherent $adherent): array
    {
        return [
            'assembly' => $this->generateAssembly($adherent),
            'circonscription' => $this->generateCirconscription($adherent),
            'committee' => $this->generateCommittee($adherent),
            'agoras' => $this->generateAgoras($adherent),
        ];
    }

    private function generateAssembly(Adherent $adherent): ?array
    {
        if (!$assemblyZone = $adherent->getAssemblyZone()) {
            return null;
        }

        return [
            'type' => 'assembly',
            'code' => $assemblyZone->getCode(),
            'name' => \sprintf(
                '%s%s',
                $assemblyZone->getName(),
                $assemblyZone->isDepartment() ? \sprintf(' (%s)', $assemblyZone->getCode()) : ''
            ),
        ];
    }

    private function generateCirconscription(Adherent $adherent): ?array
    {
        $districtZone = ($adherent->getZonesOfType(Zone::DISTRICT)[0] ?? null);

        if (!$districtZone) {
            return null;
        }

        $code = explode('-', $districtZone->getCode());
        $name = explode(' (', $districtZone->getName());

        return [
            'type' => 'circonscription',
            'code' => $districtZone->getCode(),
            'name' => \sprintf(
                '%s%s circonscription • %s (%s)',
                $code[1],
                $code[1] > 1 ? 'ème' : 'ère',
                $name[0],
                $districtZone->getCode()
            ),
        ];
    }

    private function generateCommittee(Adherent $adherent): array
    {
        $currentCommittee = $adherent->getCommitteeMembership()?->getCommittee();

        $recentElectionParticipation = $currentCommittee && $this->voterRepository->isInVoterListForCommitteeElection(
            $adherent,
            $currentCommittee,
            new \DateTime('-3 months')
        );

        return [
            'type' => 'committee',
            'name' => $currentCommittee?->getName(),
            'uuid' => $currentCommittee?->getUuid(),
            'members_count' => $currentCommittee?->getMembersCount(),
            'assembly_committees_count' => \count($this->committeeRepository->findInAdherentZone($adherent)),
            'can_change_committee' => !$recentElectionParticipation,
            'message' => $recentElectionParticipation ? 'Vous avez participé à une élection interne il y a moins de 3 mois dans votre comité. Il ne vous est pas possible d\'en changer.' : null,
        ];
    }

    private function generateAgoras(Adherent $adherent): array
    {
        return array_filter(array_map(function (AgoraMembership $agoraMembership): ?array {
            $agora = $agoraMembership->agora;

            if (!$agora?->published) {
                return null;
            }

            return [
                'type' => 'agora',
                'uuid' => $agora->getUuid(),
                'name' => $agora->getName(),
                'slug' => $agora->getSlug(),
                'description' => $agora->description,
                'max_members_count' => $agora->maxMembersCount,
                'members_count' => $agora->getMembersCount(),
            ];
        }, $adherent->agoraMemberships->toArray()));
    }
}
