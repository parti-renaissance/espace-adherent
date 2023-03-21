<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\Election;
use App\Form\TerritorialCouncil\CandidacyQualityType;
use App\Form\VotingPlatform\Candidacy\TerritorialCouncilCandidacyType;
use App\TerritorialCouncil\CandidacyManager;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/conseil-territorial/candidature', name: 'app_territorial_council_candidature')]
#[IsGranted('ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE')]
class CandidatureController extends AbstractController
{
    private $manager;

    public function __construct(CandidacyManager $manager)
    {
        $this->manager = $manager;
    }

    #[Route(path: '', name: '_edit', methods: ['POST', 'GET'])]
    public function editCandidatureAction(Request $request): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater ou modifier votre candidature pour cette désignation.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $candidacy = $membership->getCandidacyForElection($election);

        if (!$candidacy) {
            /** @var Adherent $adherent */
            $candidacyGender = $adherent->getGender();

            if ($adherent->isOtherGender()) {
                $candidacyGender = $request->query->get('gender');

                if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                    $this->addFlash('error', 'Le genre de la candidature n\'a pas été sélectionné');

                    return $this->redirectToRoute('app_territorial_council_index');
                }
            }

            $candidacy = new Candidacy($membership, $election, $candidacyGender);
        }

        $form = $this
            ->createForm(
                TerritorialCouncilCandidacyType::class,
                $candidacy,
                [
                    'image_path' => $candidacy->getImagePath(),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $isCreation = null === $candidacy->getId();
            $this->manager->updateCandidature($candidacy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            if ($candidacy->hasPendingInvitation() || !$isCreation) {
                return $this->redirectToRoute('app_territorial_council_index');
            }

            return $this->redirectToRoute('app_territorial_council_candidature_select_pair_candidate');
        }

        return $this->render('territorial_council/candidacy_step1_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/retirer', name: '_remove', methods: ['GET'])]
    public function removeCandidacyAction(): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $this->manager->removeCandidacy($candidacy);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        return $this->redirectToRoute('app_territorial_council_index');
    }

    #[Route(path: '/invitation', name: '_select_pair_candidate', methods: ['GET', 'POST'])]
    public function selectPairCandidateAction(Request $request): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_candidature_edit');
        }

        if ($candidacy->hasInvitation()) {
            $previouslyInvitedMemberships = [];

            foreach ($candidacy->getInvitations() as $invitation) {
                $previouslyInvitedMemberships[] = $invitation->getMembership();
            }
        }

        $form = $this
            ->createForm(
                CandidacyQualityType::class,
                $candidacy,
                [
                    'qualities' => $membership->getAvailableForCandidacyQualityNames(),
                    'validation_groups' => [
                        'Default',
                        DesignationTypeEnum::COPOL === $election->getDesignationType() ? 'copol_election' : 'national_council_election',
                    ],
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($invitations = $candidacy->getInvitations()) {
                $this->manager->updateInvitation($candidacy, $invitations, $previouslyInvitedMemberships ?? []);

                $this->addFlash('info', 'Votre invitation a bien été envoyée');

                return $this->redirectToRoute('app_territorial_council_candidature_select_pair_candidate_finish');
            }

            $this->manager->saveSingleCandidature($candidacy, $previouslyInvitedMemberships ?? []);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_step2_invitation.html.twig', [
            'form' => $form->createView(),
            'invitation' => $candidacy->getFirstInvitation(),
            'candidacy' => $candidacy,
            'election' => $election,
        ]);
    }

    #[Route(path: '/fini', name: '_select_pair_candidate_finish', methods: ['GET'])]
    public function finishInvitationStepAction(): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($candidacy = $membership->getCandidacyForElection($election)) || !$candidacy->hasInvitation()) {
            return $this->redirectToRoute('app_territorial_council_candidature_edit');
        }

        return $this->render('territorial_council/candidacy_step3_confirmation.html.twig', [
            'invitations' => $candidacy->getInvitations(),
            'election' => $election,
        ]);
    }

    #[Route(path: '/mes-invitations/{uuid}/accepter', name: '_invitation_accept', methods: ['GET', 'POST'])]
    #[Security('invitation.getMembership() == user.getTerritorialCouncilMembership()')]
    public function acceptInvitationAction(Request $request, CandidacyInvitation $invitation): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $acceptedBy = $membership->getCandidacyForElection($election) ?? new Candidacy($membership, $election, $adherent->getGender());

        if ($acceptedBy->isConfirmed()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        $invitedBy = $invitation->getCandidacy();

        if ($invitedBy->isConfirmed()) {
            $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer dans quelques instants.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $acceptedBy->setFaithStatement($invitedBy->getFaithStatement());
        $acceptedBy->setIsPublicFaithStatement($invitedBy->isPublicFaithStatement());

        $form = $this
            ->createForm(TerritorialCouncilCandidacyType::class, $acceptedBy, ['image_path' => $acceptedBy->getImagePath()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->acceptInvitation($invitation, $acceptedBy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_step1_edit.html.twig', [
            'form' => $form->createView(),
            'candidacy' => $acceptedBy,
            'invitation' => $invitation,
        ]);
    }

    #[Route(path: '/mes-invitations/{uuid}/decliner', name: '_invitation_decline', methods: ['GET'])]
    #[Security('invitation.getMembership() == user.getTerritorialCouncilMembership()')]
    public function declineInvitationAction(CandidacyInvitation $invitation): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $membership = $adherent->getTerritorialCouncilMembership();
        $council = $membership->getTerritorialCouncil();

        /** @var Election $election */
        if (!($election = $council->getCurrentElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!$invitation->isPending()) {
            $this->addFlash('error', 'Vous ne pouvez pas décliner cette invitation');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $this->manager->declineInvitation($invitation);

        $this->addFlash('info', 'Invitation a bien été déclinée.');

        return $this->redirectToRoute('app_territorial_council_index');
    }
}
