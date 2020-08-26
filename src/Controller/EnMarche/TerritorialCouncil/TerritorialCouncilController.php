<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Repository\TerritorialCouncil\CandidacyRepository;
use App\TerritorialCouncil\ElectionPoll\Manager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        if ($council->isFof()) {
            throw $this->createNotFoundException();
        }

        return $this->render('territorial_council/index.html.twig');
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     */
    public function faqAction(): Response
    {
        return $this->render('territorial_council/faq.html.twig');
    }

    /**
     * @Route("/membres", name="members", methods={"GET"})
     */
    public function listMembersAction(): Response
    {
        $this->disableInProduction();

        return $this->render('territorial_council/members.html.twig');
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

    /**
     * @Route("/{uuid}/sondage", name="election_poll_save_vote", methods={"POST"})
     *
     * @param Adherent $adherent
     */
    public function electionPollAction(
        Request $request,
        Poll $electionPoll,
        UserInterface $adherent,
        Manager $voteManager
    ): Response {
        if ($voteManager->hasVoted($electionPoll, $membership = $adherent->getTerritorialCouncilMembership())) {
            $this->addFlash('error', 'Vous avez déjà participé à ce sondage.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($choiceUuid = $request->request->get('poll-choice')) || !Uuid::isValid($choiceUuid) || !($choice = $voteManager->findChoice($choiceUuid))) {
            $this->addFlash('error', 'Choix est invalide.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $voteManager->vote($choice, $membership);

        $this->addFlash('info', 'Votre participation au sondage a bien été enregistrée.');

        return $this->redirectToRoute('app_territorial_council_index');
    }
}
