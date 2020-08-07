<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use App\Form\TerritorialCouncil\CandidacyQualityType;
use App\Form\VotingPlatform\Candidacy\TerritorialCouncilCandidacyType;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\TerritorialCouncil\CandidacyManager;
use App\ValueObject\Genders;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/conseil-territorial/candidature", name="app_territorial_council_candidature")
 *
 * @Security("is_granted('TERRITORIAL_COUNCIL_MEMBER')")
 */
class CandidatureController extends Controller
{
    use CanaryControllerTrait;

    private $manager;

    public function __construct(CandidacyManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("", name="_edit", methods={"POST", "GET"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function editCandidatureAction(Request $request, UserInterface $adherent): Response
    {
        $this->disableInProduction();

        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater pour cette désignation.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        /** @var Adherent $adherent */
        $candidacyGender = $adherent->getGender();

        if ($adherent->isOtherGender()) {
            $candidacyGender = $request->query->get('gender');

            if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                $this->addFlash('error', 'Le genre de la candidature n\'a pas été sélectionné');

                return $this->redirectToRoute('app_territorial_council_index');
            }
        }

        $candidacy = $membership->getCandidacyForElection($election) ?? new Candidacy($membership, $election, $candidacyGender);

        $form = $this
            ->createForm(TerritorialCouncilCandidacyType::class, $candidacy)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->updateCandidature($candidacy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_territorial_council_candidature_select_pair_candidate');
        }

        return $this->render('territorial_council/edit_candidacy.html.twig', [
            'form' => $form->createView(),
            'territorial_council' => $council,
            'candidacy' => $candidacy,
        ]);
    }

    /**
     * @Route("/retirer", name="_remove", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function removeCandidacyAction(UserInterface $adherent): Response
    {
        $this->disableInProduction();

        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!$candidacy = $membership->getCandidacyForElection($election)) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $this->manager->removeCandidacy($candidacy);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        return $this->redirectToRoute('app_territorial_council_index');
    }

    /**
     * @Route("/choix-de-binome", name="_select_pair_candidate", methods={"GET", "POST"})
     *
     * @param Adherent $adherent
     */
    public function selectPairCandidateAction(
        Request $request,
        UserInterface $adherent,
        TerritorialCouncilMembershipRepository $membershipRepository
    ): Response {
        $this->disableInProduction();

        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!$candidacy = $membership->getCandidacyForElection($election)) {
            return $this->redirectToRoute('app_territorial_council_candidature_edit');
        }

        $form = $this
            ->createForm(
                CandidacyQualityType::class,
                $candidacy,
                [
                    'memberships' => $membershipRepository->findAvailableMemberships($membership),
                    'qualities' => $membership->getQualityNames(),
                ]
            )
            ->handleRequest($request)
        ;

        return $this->render('territorial_council/select_pair_candidate.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'territorial_council' => $council,
        ]);
    }
}
