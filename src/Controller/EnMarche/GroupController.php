<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/groupes/{slug}")
 */
class GroupController extends Controller
{
    /**
     * @Route(name="app_group_show")
     * @Method("GET|POST")
     */
    public function showAction(Request $request, Group $group): Response
    {
        return new Response();
    }
}
