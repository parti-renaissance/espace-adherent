<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur/mon-equipe", name="app_senator_my_team_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_SENATOR')")
 */
class SenatorMyTeamController extends AbstractMyTeamController
{
    private const SPACE_NAME = 'senator';

    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForSenator($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getSenatorArea()->getDepartmentTag()];
    }

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
