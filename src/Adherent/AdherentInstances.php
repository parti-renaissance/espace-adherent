<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\AgoraMembership;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\Scope\ScopeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentInstances
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly VoterRepository $voterRepository,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
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

        $pad = $this->adherentRepository->findZoneManager($assemblyZone, ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);

        return [
            'type' => 'assembly',
            'code' => $assemblyZone->getCode(),
            'name' => \sprintf(
                '%s%s',
                $assemblyZone->getName(),
                $assemblyZone->isDepartment() ? \sprintf(' (%s)', $assemblyZone->getCode()) : ''
            ),
            'manager' => $pad ? $this->generateManager($pad, $this->trans('role.president_departmental_assembly')) : null,
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

        $deputy = $this->adherentRepository->findZoneManager($districtZone, ScopeEnum::DEPUTY);

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
            'manager' => $deputy ? $this->generateManager($deputy, $this->trans('role.deputy')) : null,
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

        $animator = $currentCommittee?->animator;

        return [
            'type' => 'committee',
            'name' => $currentCommittee?->getName(),
            'uuid' => $currentCommittee?->getUuid(),
            'members_count' => $currentCommittee?->getMembersCount(),
            'assembly_committees_count' => \count($this->committeeRepository->findInAdherentZone($adherent)),
            'can_change_committee' => !$recentElectionParticipation,
            'message' => $recentElectionParticipation ? 'Vous avez participé à une élection interne il y a moins de 3 mois dans votre comité. Il ne vous est pas possible d\'en changer.' : null,
            'manager' => $animator ? $this->generateManager($animator, $this->trans('role.animator')) : null,
        ];
    }

    private function generateAgoras(Adherent $adherent): array
    {
        return array_filter(array_map(function (AgoraMembership $agoraMembership): ?array {
            $agora = $agoraMembership->agora;

            if (!$agora?->published) {
                return null;
            }

            $president = $agora->president;

            return [
                'type' => 'agora',
                'uuid' => $agora->getUuid(),
                'name' => $agora->getName(),
                'slug' => $agora->getSlug(),
                'description' => $agora->description,
                'max_members_count' => $agora->maxMembersCount,
                'members_count' => $agora->getMembersCount(),
                'manager' => $president ? $this->generateManager($president, $this->trans('role.agora_president')) : null,
            ];
        }, $adherent->agoraMemberships->toArray()));
    }

    private function generateManager(Adherent $adherent, string $role): array
    {
        return [
            'uuid' => $adherent->getUuidAsString(),
            'public_id' => $adherent->getPublicId(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'image_url' => $adherent->getImageName() ? $this->urlGenerator->generate(
                'asset_url',
                ['path' => $adherent->getImagePath()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) : null,
            'role' => $role,
        ];
    }

    private function trans(string $key): string
    {
        return $this->translator->trans($key);
    }
}
