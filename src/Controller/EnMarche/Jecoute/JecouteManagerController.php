<?php

namespace AppBundle\Controller\EnMarche\Jecoute;

use AppBundle\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

    protected function getLocalSurveys(): array
    {
        return $this->localSurveyRepository->findAllByTagsWithStats($this->getSurveyTags());
    }

    protected function getSurveyTags(): array
    {
        return $this->getUser()->getJecouteManagedArea()->getCodes();
    }
}
