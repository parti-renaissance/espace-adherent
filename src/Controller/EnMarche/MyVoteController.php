<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted('ROLE_ADHERENT')]
#[Route(path: '/mon-vote', name: 'app_adherent_my_vote', methods: ['GET'])]
class MyVoteController extends AbstractController
{
    use CanaryControllerTrait;

    public function __invoke(): Response
    {
        $this->disableInProduction();

        return $this->render('adherent/my_vote.html.twig');
    }
}
