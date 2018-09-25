<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Repository\Biography\ExecutiveOfficeMemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BiographyController extends Controller
{
    /**
     * @Route("/le-mouvement/notre-organisation", name="app_our_organization")
     * @Method("GET")
     */
    public function executiveOfficeMemberListAction(ExecutiveOfficeMemberRepository $repository): Response
    {
        $allMembers = $repository->findAllExecutiveOfficeMembers();

        return $this->render('page/le-mouvement/notre-organisation.html.twig', [
            'executiveOfficeMembers' => $allMembers->getExecutiveOfficeMembers(),
            'executiveOfficer' => $allMembers->getExecutiveOfficer(),
        ]);
    }
}
