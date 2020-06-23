<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senatoriales/mon-equipe", name="app_senatorial_candidate_my_team_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_SENATORIAL_CANDIDATE')")
 */
class SenatorialCandidateMyTeamController extends AbstractMyTeamController
{
    protected function getSpaceType(): string
    {
        return 'senatorial_candidate';
    }

    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForSenatorialCandidate($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return $adherent->getSenatorialCandidateManagedArea()->getDepartmentTags()->toArray();
    }
}
