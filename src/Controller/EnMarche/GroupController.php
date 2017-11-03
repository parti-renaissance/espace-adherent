<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/groupes")
 */
class GroupController extends Controller
{
    /**
     * @Route("/{slug}", name="app_group_show")
     * @Method("GET|POST")
     * @Entity("group", expr="repository.findOneApprovedBySlug(slug)")
     */
    public function showAction(Group $group): Response
    {
        return new Response();
    }
}
