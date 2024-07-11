<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_REFERENT')]
#[Route(path: '/espace-referent/mon-equipe', name: 'app_referent_my_team_', methods: ['GET'])]
class ReferentMyTeamController extends AbstractMyTeamController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }

    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForReferent($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return $adherent->getManagedArea()->getTags()->toArray();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
