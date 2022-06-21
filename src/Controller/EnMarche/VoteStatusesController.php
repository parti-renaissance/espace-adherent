<?php

namespace App\Controller\EnMarche;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/changement-des-statuts", name="app_vote_statuses")
 */
class VoteStatusesController extends AbstractController
{
    /**
     * @Route("", name="_index")
     */
    public function indexAction(): Response
    {
        return $this->render('vote_statuses/index.html.twig');
    }

    /**
     * @Route("reglement", name="_regulation")
     */
    public function regulationAction(): Response
    {
        return $this->render('vote_statuses/regulation.html.twig');
    }
}
