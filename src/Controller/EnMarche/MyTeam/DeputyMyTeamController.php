<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute/mon-equipe", name="app_deputy_my_team_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyMyTeamController extends AbstractMyTeamController
{
    private const SPACE_NAME = 'deputy';

    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForDeputy($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getManagedDistrict()->getReferentTag()];
    }

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
