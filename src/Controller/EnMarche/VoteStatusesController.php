<?php

namespace App\Controller\EnMarche;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/changement-des-statuts', name: 'app_vote_statuses')]
#[IsGranted('ROLE_STATUSES_VOTER')]
class VoteStatusesController extends AbstractController
{
    #[Route(path: '', name: '_index')]
    public function indexAction(): Response
    {
        return $this->render('vote_statuses/index.html.twig');
    }

    #[Route(path: '/reglement', name: '_regulation')]
    public function regulationAction(): Response
    {
        return $this->render('vote_statuses/regulation.html.twig');
    }
}
