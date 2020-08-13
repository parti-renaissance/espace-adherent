<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\Adherent;
use App\Repository\TerritorialCouncil\CandidacyRepository;
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
    /**
     * @Route("", name="index", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function indexAction(UserInterface $adherent): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        if ($council->isFof()) {
            throw $this->createNotFoundException();
        }

        return $this->render('territorial_council/index.html.twig');
    }

    /**
     * @Route("/liste-candidature", name="candidacy_list", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function candidacyListAction(UserInterface $adherent, CandidacyRepository $repository): Response
    {
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        if (!$election = $council->getCurrentElection()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_list.html.twig', [
            'candidacies' => $repository->findAllConfirmedForElection($election),
        ]);
    }
}
