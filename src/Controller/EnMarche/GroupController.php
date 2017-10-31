<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/groupes")
 */
class GroupController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/{slug}", name="app_group_show")
     * @Method("GET|POST")
     * @Security("is_granted('SHOW_GROUP', group)")
     */
    public function showAction(Group $group): Response
    {
        $this->disableInProduction();

        return new Response();
    }

    /**
     * @Route("/aide", name="app_group_help")
     * @Method("GET|POST")
     */
    public function helpAction(): Response
    {
        $this->disableInProduction();

        return new Response();
    }
}
