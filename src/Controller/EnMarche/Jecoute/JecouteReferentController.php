<?php

namespace AppBundle\Controller\EnMarche\Jecoute;

use AppBundle\Jecoute\JecouteSpaceEnum;
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
}
