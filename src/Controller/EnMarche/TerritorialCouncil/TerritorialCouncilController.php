<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/conseil-territorial", name="app_territorial_council_")
 *
 * @Security("is_granted('TERRITORIAL_COUNCIL_MEMBER')")
 */
class TerritorialCouncilController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("", name="index", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function indexAction(UserInterface $adherent): Response
    {
        $this->disableInProduction();

        if (!$membership = $adherent->getTerritorialCouncilMembership()) {
            throw $this->createNotFoundException('This user is not member of a territorial council.');
        }

        $council = $membership->getTerritorialCouncil();
        $election = $council->getCurrentElection();

        return $this->render('territorial_council/index.html.twig', [
            'territorial_council' => $council,
            'candidacy' => $election ? $membership->getCandidacyForElection($election) : null,
        ]);
    }
}
