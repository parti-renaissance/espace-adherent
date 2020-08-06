<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/conseil-territorial", name="app_territorial_council_")
 */
class TerritorialCouncilController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("", name="index")
     */
    public function indexAction(UserInterface $user)
    {
        $this->disableInProduction();

        if (!$user->getTerritorialCouncilMembership()) {
            throw $this->createNotFoundException('This user is not member of a territorial council.');
        }

        $territorialCouncil = $user->getTerritorialCouncilMembership()->getTerritorialCouncil();

        return $this->render('territorial_council/index.html.twig', [
            'territorial_council' => $territorialCouncil,
        ]);
    }
}
