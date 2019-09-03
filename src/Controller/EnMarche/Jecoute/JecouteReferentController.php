<?php

namespace AppBundle\Controller\EnMarche\Jecoute;

use AppBundle\Jecoute\JecouteSpaceEnum;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/jecoute", name="app_jecoute_referent_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class JecouteReferentController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::REFERENT_SPACE;
    }

    protected function getLocalSurveys(LocalSurveyRepository $localSurveyRepository): array
    {
        return $localSurveyRepository->findAllByTags($this->getSurveyTags());
    }

    protected function getSurveyTags(): array
    {
        return $this->getUser()->getManagedAreaTagCodes();
    }
}
