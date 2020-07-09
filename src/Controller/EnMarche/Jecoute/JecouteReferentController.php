<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/jecoute", name="app_jecoute_referent_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE'))")
 */
class JecouteReferentController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::REFERENT_SPACE;
    }

    protected function getLocalSurveys(Adherent $adherent): array
    {
        return $this->localSurveyRepository->findAllByTagsWithStats($this->getSurveyTags($adherent));
    }

    protected function getSurveyTags(Adherent $adherent): array
    {
        return $adherent->getManagedAreaTagCodes();
    }
}
