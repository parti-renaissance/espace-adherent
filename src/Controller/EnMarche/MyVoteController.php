<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mon-vote", name="app_adherent_my_vote", methods={"GET"})
 *
 * @Security("is_granted('ROLE_ADHERENT')")
 */
class MyVoteController extends Controller
{
    use CanaryControllerTrait;

    public function __invoke(): Response
    {
        $this->disableInProduction();

        return $this->render('adherent/my_vote.html.twig');
    }
}
