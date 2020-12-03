<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020/questionnaires", name="app_jecoute_municipal_chief_")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF') and user.municipalChiefManagedArea.hasJecouteAccess()")
 */
class MunicipalChiefJecouteController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::MUNICIPAL_CHIEF_SPACE;
    }

    protected function getLocalSurveys(Adherent $adherent): array
    {
        return $this->localSurveyRepository->findAllByAuthor($adherent);
    }

    protected function getZones(Adherent $adherent): array
    {
        return $this->zoneRepository->findBy(['code' => $adherent->getMunicipalChiefManagedArea()->getDepartmentalCode()]);
    }
}
