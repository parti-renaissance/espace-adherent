<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-candidat/questionnaires", name="app_jecoute_candidate_")
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE'))")
 */
class JecouteCandidateController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::CANDIDATE_SPACE;
    }

    protected function getLocalSurveys(Adherent $adherent): array
    {
        return $this->localSurveyRepository->findAllByZonesWithStats($this->getZones($adherent));
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}
