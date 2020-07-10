<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-jecoute", name="app_jecoute_manager_")
 *
 * @Security("is_granted('ROLE_JECOUTE_MANAGER')")
 */
class JecouteManagerController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::MANAGER_SPACE;
    }

    protected function getLocalSurveys(Request $request): array
    {
        return $this->localSurveyRepository->findAllByTagsWithStats($this->getSurveyTags($request));
    }

    protected function getSurveyTags(Request $request): array
    {
        return $this->getUser()->getJecouteManagedArea()->getCodes();
    }
}
