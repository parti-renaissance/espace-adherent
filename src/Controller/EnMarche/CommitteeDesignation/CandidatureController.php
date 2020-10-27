<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Committee\Election\CandidacyManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Form\VotingPlatform\Candidacy\CommitteeCandidacyType;
use App\Security\Voter\Committee\CommitteeCandidacyVoter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

            $candidacy = new CommitteeCandidacy($election, $candidacyGender);
        }

        $isCreation = null === $candidacy->getId();

        $form = $this->createForm(CommitteeCandidacyType::class, $candidacy);

        if ($isCreation) {
            $form->add('skip', SubmitType::class);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->candidatureManager->updateCandidature($candidacy, $adherent, $committee);

            $this->addFlash('info', 'Votre candidature a bien été '.($isCreation ? 'enregistrée' : 'modifiée'));

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/edit_candidacy.html.twig', [
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
}
