<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-comite-local", name="app_my_committee_show_current", methods={"GET"})
 */
class ShowMyCurrentCommitteeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/adherent/my_committee/show_my_current_committee.html.twig');
    }
}
