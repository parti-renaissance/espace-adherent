<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Committee\Election\CandidacyManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacyInvitation;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Form\Committee\CandidacyBinomeType;
use App\Form\VotingPlatform\Candidacy\CommitteeCandidacyType;
use App\Form\VotingPlatform\Candidacy\CommitteeSupervisorCandidacyType;
use App\Repository\CommitteeCandidacyInvitationRepository;
use App\Repository\CommitteeCandidacyRepository;
use App\Security\Voter\Committee\CommitteeElectionVoter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('MEMBER_OF_COMMITTEE', subject: 'committee')]
#[Route(path: '/comites/{slug}/candidature', name: 'app_committee_candidature')]
class CandidatureController extends AbstractController
{
    private $candidatureManager;

    public function __construct(CandidacyManager $candidatureManager)
    {
        $this->candidatureManager = $candidatureManager;
    }

    #[Route(path: '', name: '_edit', methods: ['GET', 'POST'])]
    public function candidateAction(Committee $committee, Request $request): Response
    {
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater ou modifier votre candidature pour cette désignation.');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        $candidacy = $this->candidatureManager->getCandidacy($adherent, $committee);

        if (!$candidacy) {
            /** @var Adherent $adherent */
            $candidacyGender = $adherent->getGender();

            if ($adherent->isOtherGender()) {
                $candidacyGender = $request->query->get('gender');

                if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                    $this->addFlash('error', 'La civilité de la candidature n\'a pas été sélectionné');

                    return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
                }
            }

            $candidacy = $this->candidatureManager->createCandidacy($election, $candidacyGender);
        }

        $isCreation = null === $candidacy->getId();

        $form = $this
            ->createCandidacyForm($candidacy, ['image_path' => $candidacy->getImagePath()])
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

        return $this->render(\sprintf('committee/candidacy/form_%s.html.twig', $election->getDesignationType()), [
            'candidacy' => $candidacy,
            'form' => $form->createView(),
            'committee' => $committee,
        ]);
    }

    #[Route(path: '/retirer', name: '_remove', methods: ['GET'])]
    public function removeCandidacy(Request $request, Committee $committee): Response
    {
        if (!$committee->getCommitteeElection() || !$committee->getCommitteeElection()->isCandidacyPeriodActive()) {
            if (!(false === $committee->getCommitteeElection()->isVotePeriodStarted() && $this->isGranted('IS_IMPERSONATOR'))) {
                $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        $this->candidatureManager->removeCandidacy($this->getUser(), $committee);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        if ($request->query->has('back')) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->redirectToRoute('app_adherent_committees');
    }

    #[Route(path: '/choix-de-binome', name: '_select_pair_candidate', methods: ['GET', 'POST'])]
    public function selectPairCandidateAction(Committee $committee, Request $request): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (!($candidacy = $this->candidatureManager->getCandidacy($adherent, $committee)) || $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        if ($candidacy->hasInvitation()) {
            $invitation = $candidacy->getFirstInvitation();

            if ($invitation->isAccepted()) {
                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }

            $previouslyInvitedMembership = $invitation->getMembership();
        }

        $form = $this
            ->createForm(CandidacyBinomeType::class, $candidacy)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->candidatureManager->updateInvitation(
                $candidacy->getFirstInvitation(),
                $candidacy,
                $previouslyInvitedMembership ?? null
            );

            $this->addFlash('info', 'Votre invitation a bien été envoyée');

            return $this->redirectToRoute('app_committee_candidature_select_pair_candidate_finish', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/candidacy_invitation.html.twig', [
            'form' => $form->createView(),
            'invitation' => $candidacy->getFirstInvitation(),
            'committee' => $committee,
        ]);
    }

    #[Route(path: '/choix-de-binome/fini', name: '_select_pair_candidate_finish', methods: ['GET'])]
    public function finishInvitationStepAction(Committee $committee): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (!($candidacy = $this->candidatureManager->getCandidacy($adherent, $committee)) || !($invitation = $candidacy->getFirstInvitation())) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/candidacy_invitation_confirmation.html.twig', [
            'invitation' => $invitation,
            'committee' => $committee,
        ]);
    }

    #[Route(path: '/mes-invitations', name: '_invitation_list', methods: ['GET'])]
    public function invitationListAction(
        Committee $committee,
        CommitteeCandidacyInvitationRepository $repository,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        if (($candidacy = $this->candidatureManager->getCandidacy($adherent, $committee)) && $candidacy->isConfirmed()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        return $this->render('committee/candidacy/invitation_list.html.twig', [
            'invitations' => $repository->findAllPendingForMembership($adherent->getMembershipFor($committee), $election),
            'committee' => $committee,
            'candidacy' => $candidacy,
        ]);
    }

    #[IsGranted(new Expression('subject.getMembership() == user.getMembershipFor(committee)'), subject: 'invitation')]
    #[Route(path: '/mes-invitations/{uuid}/accepter', name: '_invitation_accept', methods: ['GET', 'POST'])]
    public function acceptInvitationAction(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Committee $committee,
        Request $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])]
        CommitteeCandidacyInvitation $invitation,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        $acceptedBy = $this->candidatureManager->getCandidacy($adherent, $committee, true);

        if ($acceptedBy->isConfirmed()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $invitedBy = $invitation->getCandidacy();
        $acceptedBy->setFaithStatement($invitedBy->getFaithStatement());
        $acceptedBy->setIsPublicFaithStatement($invitedBy->isPublicFaithStatement());

        $form = $this
            ->createCandidacyForm($acceptedBy, ['validation_groups' => ['Default', 'accept_invitation']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->candidatureManager->acceptInvitation($invitation, $acceptedBy);

            $this->addFlash('info', 'Votre candidature a bien été enregistrée');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/form_committee_supervisor.html.twig', [
            'form' => $form->createView(),
            'candidacy' => $acceptedBy,
            'invitation' => $invitation,
            'committee' => $committee,
        ]);
    }

    #[IsGranted(new Expression('subject.getMembership() == user.getMembershipFor(committee)'), subject: 'invitation')]
    #[Route(path: '/mes-invitations/{uuid}/decliner', name: '_invitation_decline', methods: ['GET'])]
    public function declineInvitationAction(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Committee $committee,
        #[MapEntity(mapping: ['uuid' => 'uuid'])]
        CommitteeCandidacyInvitation $invitation,
    ): Response {
        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        if (!$invitation->isPending()) {
            $this->addFlash('error', 'Vous ne pouvez pas décliner cette invitation');

            return $this->redirectToRoute('app_committee_candidature_invitation_list');
        }

        $this->candidatureManager->declineInvitation($invitation);

        $this->addFlash('info', 'Invitation a bien été déclinée');

        return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
    }

    #[Route(path: '/liste', name: '_candidacy_list', methods: ['GET'])]
    public function candidacyListAction(Committee $committee, CommitteeCandidacyRepository $repository): Response
    {
        if (!($election = $committee->getCommitteeElection()) || $election->isVotePeriodStarted()) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/candidacy/candidacy_list.html.twig', [
            'candidacies' => $repository->findAllConfirmedForElection($election),
            'election' => $election,
            'committee' => $committee,
            'designation' => $election->getDesignation(),
        ]);
    }

    private function createCandidacyForm(CandidacyInterface $candidacy, array $options = []): FormInterface
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $candidacy->getType()) {
            return $this->createForm(
                CommitteeSupervisorCandidacyType::class,
                $candidacy,
                array_merge_recursive($options, ['validation_groups' => ['Default', 'committee_supervisor_candidacy']])
            );
        }

        $form = $this->createForm(CommitteeCandidacyType::class, $candidacy, $options);

        if (!$candidacy->getId()) {
            $form->add('skip', SubmitType::class);
        }

        return $form;
    }
}
