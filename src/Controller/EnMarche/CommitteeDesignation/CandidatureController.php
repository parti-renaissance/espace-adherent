<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Committee\Election\CandidacyManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Form\Committee\CandidacyBinomeType;
use App\Form\VotingPlatform\Candidacy\CommitteeCandidacyType;
use App\Form\VotingPlatform\Candidacy\CommitteeSupervisorCandidacyType;
use App\Security\Voter\Committee\CommitteeCandidacyVoter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/comites/{slug}/candidature", name="app_committee_candidature")
 *
 * @Security("is_granted('MEMBER_OF_COMMITTEE', committee)")
 */
class CandidatureController extends AbstractController
{
    private $candidatureManager;

    public function __construct(CandidacyManager $candidatureManager)
    {
        $this->candidatureManager = $candidatureManager;
    }

    /**
     * @Route("", name="_edit", methods={"GET", "POST"})
     *
     * @param UserInterface|Adherent $adherent
     */
    public function candidateAction(UserInterface $adherent, Committee $committee, Request $request): Response
    {
        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater ou modifier votre candidature pour cette désignation.');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            $this->denyAccessUnlessGranted(CommitteeCandidacyVoter::PERMISSION);
        }

        $candidacy = $this->candidatureManager->getCandidacy($adherent, $committee);

        if (!$candidacy) {
            /** @var Adherent $adherent */
            $candidacyGender = $adherent->getGender();

            if ($adherent->isOtherGender()) {
                $candidacyGender = $request->query->get('gender');

                if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                    $this->addFlash('error', 'Le genre de la candidature n\'a pas été sélectionné');

                    return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
                }
            }

            $candidacy = $this->candidatureManager->createCandidacy($election, $candidacyGender);
        }

        $isCreation = null === $candidacy->getId();

        $form = $this
            ->createCandidacyForm($candidacy)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->candidatureManager->updateCandidature($candidacy, $adherent, $committee);

            $this->addFlash('info', 'Votre candidature a bien été '.($isCreation ? 'enregistrée' : 'modifiée'));

            if (
                DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()
                || $candidacy->hasPendingInvitation()
                || !$isCreation
            ) {
                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }

            return $this->redirectToRoute('app_committee_candidature_select_pair_candidate', ['slug' => $committee->getSlug()]);
        }

        return $this->render(sprintf('committee/candidacy/form_%s.html.twig', $election->getDesignationType()), [
            'candidacy' => $candidacy,
            'form' => $form->createView(),
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/retirer", name="_remove", methods={"GET"})
     */
    public function removeCandidacy(Request $request, Committee $committee): Response
    {
        if (!$committee->getCommitteeElection() || !$committee->getCommitteeElection()->isCandidacyPeriodActive()) {
            if (!(false === $committee->getCommitteeElection()->isVotePeriodStarted() && $this->isGranted('ROLE_PREVIOUS_ADMIN'))) {
                $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }
        }

        $this->candidatureManager->removeCandidacy($this->getUser(), $committee);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        if ($request->query->has('back')) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->redirectToRoute('app_adherent_committees');
    }

    /**
     * @Route("/choix-de-binome", name="_select_pair_candidate", methods={"GET", "POST"})
     *
     * @param Adherent $adherent
     */
    public function selectPairCandidateAction(Committee $committee, Request $request, UserInterface $adherent): Response
    {
        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (!($candidacy = $this->candidatureManager->getCandidacy($adherent, $committee)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if ($candidacy->hasInvitation()) {
            if ($candidacy->getInvitation()->isAccepted()) {
                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }

            $previouslyInvitedMembership = $candidacy->getInvitation()->getMembership();
        }

        $form = $this
            ->createForm(CandidacyBinomeType::class, $candidacy)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $invitation = $candidacy->getInvitation();
            $this->candidatureManager->updateInvitation($invitation, $candidacy, $previouslyInvitedMembership ?? null);

            $this->addFlash('info', 'Votre invitation a bien été envoyée');

            return $this->redirectToRoute('app_committee_candidature_select_pair_candidate_finish', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/candidacy_invitation.html.twig', [
            'form' => $form->createView(),
            'invitation' => $candidacy->getInvitation(),
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/choix-de-binome/fini", name="_select_pair_candidate_finish", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function finishInvitationStepAction(Committee $committee, UserInterface $adherent): Response
    {
        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (!($candidacy = $this->candidatureManager->getCandidacy($adherent, $committee)) || !($invitation = $candidacy->getInvitation())) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/candidacy_invitation_confirmation.html.twig', [
            'invitation' => $invitation,
            'committee' => $committee,
        ]);
    }

    private function createCandidacyForm(CandidacyInterface $candidacy): FormInterface
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $candidacy->getType()) {
            return $this->createForm(CommitteeSupervisorCandidacyType::class, $candidacy);
        }

        $form = $this->createForm(CommitteeCandidacyType::class, $candidacy);

        if (!$candidacy->getId()) {
            $form->add('skip', SubmitType::class);
        }

        return $form;
    }
}
