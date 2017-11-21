<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/action-citoyenne")
 * @Entity("action", expr="repository.findOnePublishedBySlug(slug)")
 */
class CitizenActionController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/{slug}", name="app_citizen_action_show")
     * @Method("GET")
     */
    public function showAction(CitizenAction $action): Response
    {
        return new Response();
    }
}
