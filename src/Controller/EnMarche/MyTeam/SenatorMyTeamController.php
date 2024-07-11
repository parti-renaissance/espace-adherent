<?php

namespace App\Controller\EnMarche\MyTeam;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_SENATOR')]
#[Route(path: '/espace-senateur/mon-equipe', name: 'app_senator_my_team_', methods: ['GET'])]
class SenatorMyTeamController extends AbstractMyTeamController
{
    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForSenator($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getSenatorArea()->getDepartmentTag()];
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }

    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::SENATOR;
    }
}
