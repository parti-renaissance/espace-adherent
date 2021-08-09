<?php

namespace App\Controller\EnMarche\MyTeam;

use App\AdherentSpace\AdherentSpaceEnum;
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
    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        return $committeeRepository->findByPartialNameForDeputy($adherent, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getManagedDistrict()->getReferentTag()];
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }

    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::DEPUTY;
    }
}
