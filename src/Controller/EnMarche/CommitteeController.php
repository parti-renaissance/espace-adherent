<?php

namespace App\Controller\EnMarche;

use App\Committee\CommitteeManager;
use App\Controller\CanaryControllerTrait;
use App\Controller\EntityControllerTrait;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeFeedItem;
use App\Form\CommitteeFeedItemMessageType;
use App\Form\VotingPlatform\CandidacyBiographyType;
use App\Image\ImageManager;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\ValueObject\Genders;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/comites/{slug}")
 */
class CommitteeController extends Controller
{
    use EntityControllerTrait;
    use CanaryControllerTrait;

    /**
     * @Route(name="app_committee_show", methods={"GET"})
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function showAction(Request $request, Committee $committee): Response
    {
        if ($this->isGranted('IS_ANONYMOUS')
            && $authenticate = $this->get(AnonymousFollowerSession::class)->start($request)
        ) {
            return $authenticate;
        }

        if ($committee->isPending() || $committee->isPreApproved()) {
            return $this->redirectToRoute('app_committee_manager_edit', [
                'slug' => $committee->getSlug(),
            ]);
        }

        $committeeManager = $this->getCommitteeManager();

        $feeds = $committeeManager->getTimeline($committee, $this->getParameter('timeline_max_messages'));

        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'committee_timeline' => $feeds,
            'committee_timeline_forms' => $this->createTimelineDeleteForms($feeds),
            'committee_timeline_max_messages' => $this->getParameter('timeline_max_messages'),
        ]);
    }

    /**
     * @Route("/timeline/{id}/modifier", name="app_committee_timeline_edit", methods={"GET", "POST"})
     * @ParamConverter("committee", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("committeeFeedItem", options={"mapping": {"id": "id"}})
     * @Security("is_granted('ADMIN_FEED_COMMITTEE', committeeFeedItem)")
     */
    public function timelineEditAction(
        Request $request,
        Committee $committee,
        CommitteeFeedItem $committeeFeedItem
    ): Response {
        $form = $this
            ->createForm(CommitteeFeedItemMessageType::class, $committeeFeedItem)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'committee.message_edited');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/timeline/edit.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->getCommitteeManager()->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/timeline/{id}/supprimer", name="app_committee_timeline_delete", methods={"DELETE"})
     * @ParamConverter("committee", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("committeeFeedItem", options={"mapping": {"id": "id"}})
     * @Security("is_granted('ADMIN_FEED_COMMITTEE', committeeFeedItem)")
     */
    public function timelineDeleteAction(
        Request $request,
        Committee $committee,
        CommitteeFeedItem $committeeFeedItem
    ): Response {
        $form = $this->createDeleteForm('', 'committee_feed_delete', $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($committeeFeedItem);
        $em->flush();

        $this->addFlash('info', 'committee.message_deleted');

        return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
    }

    /**
     * @Route("/timeline", name="app_committee_timeline", methods={"GET"})
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function timelineAction(Request $request, Committee $committee): Response
    {
        $timeline = $this->getCommitteeManager()->getTimeline(
            $committee,
            $this->getParameter('timeline_max_messages'),
            $request->query->getInt('offset', 0)
        );

        return $this->render('committee/timeline/_feed.html.twig', [
            'committee' => $committee,
            'committee_timeline' => $timeline,
            'committee_timeline_forms' => $this->createTimelineDeleteForms($timeline),
            'has_role_adherent' => $this->getUser() instanceof Adherent && $this->getUser()->isAdherent(),
            'has_role_user' => $this->isGranted('ROLE_USER'),
        ]);
    }

    /**
     * @Route("/rejoindre", name="app_committee_follow", condition="request.request.has('token')", methods={"POST"})
     * @Security("is_granted('FOLLOW_COMMITTEE', committee)")
     */
    public function followAction(Request $request, Committee $committee): Response
    {
        if (!$this->isCsrfTokenValid('committee.follow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to follow committee.');
        }

        $this->get('app.committee.authority')->followCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Quitter ce comité',
                'action' => 'quitter',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('committee.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/quitter", name="app_committee_unfollow", condition="request.request.has('token')", methods={"POST"})
     * @Security("is_granted('UNFOLLOW_COMMITTEE', committee)")
     */
    public function unfollowAction(Request $request, Committee $committee): Response
    {
        if (!$this->isCsrfTokenValid('committee.unfollow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unfollow committee.');
        }

        $this->getCommitteeManager()->unfollowCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Suivre ce comité',
                'action' => 'rejoindre',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('committee.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/voter", defaults={"enable": true}, name="app_committee_vote", condition="request.isXmlHttpRequest()", methods={"POST"})
     * @Route("/ne-plus-voter", defaults={"enable": false}, name="app_committee_unvote", condition="request.isXmlHttpRequest()", methods={"POST"})
     *
     * @Security("is_granted('ABLE_TO_CHANGE_COMMITTEE_VOTE', committee)")
     *
     * @param Adherent $adherent
     */
    public function toggleCommitteeVoteAction(
        bool $enable,
        UserInterface $adherent,
        Request $request,
        Committee $committee,
        CommitteeManager $manager
    ): Response {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('committee.vote', $request->request->get('token'))) {
            return $this->json([
                'status' => 'NOK',
                'error' => 'Requête invalide',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($adherent->getMemberships()->getCommitteeCandidacyMembership()) {
            return $this->json([
                'status' => 'NOK',
                'error' => 'Vous devez retirer votre candidature pour pouvoir changer de comité de désignation.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $membership = $adherent->getMembershipFor($committee);

        if ($enable) {
            $manager->enableVoteInMembership($membership, $adherent);
        } else {
            $manager->disableVoteInMembership($membership);
        }

        return $this->json(['status' => 'OK'], Response::HTTP_OK);
    }

    /**
     * @Route("/candidater", name="app_committee_candidate", methods={"GET", "POST"})
     *
     * @Security("is_granted('MEMBER_OF_COMMITTEE', committee) and is_granted('ABLE_TO_CANDIDATE')")
     */
    public function candidateAction(
        UserInterface $adherent,
        Committee $committee,
        Request $request,
        CommitteeManager $manager,
        ImageManager $imageManager
    ): Response {
        $this->disableInProduction();

        if (!$committee->getCommitteeElection() || !$committee->getCommitteeElection()->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas candidater pour cette désignation.');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        /** @var Adherent $adherent */
        $candidacyGender = $adherent->getGender();

        if ($adherent->isOtherGender()) {
            $candidacyGender = $request->query->get('gender');

            if (!$candidacyGender || !\in_array($candidacyGender, Genders::CIVILITY_CHOICES, true)) {
                $this->addFlash('error', 'Le genre de la candidature n\'a pas été sélectionné');

                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }
        }

        $candidacy = new CommitteeCandidacy($committee->getCommitteeElection(), $candidacyGender);

        $form = $this
            ->createForm(CandidacyBiographyType::class, $candidacy)
            ->add('skip', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked() && $candidacy->getImage()) {
                $imageManager->saveImage($candidacy);
            } elseif ($form->get('skip')->isClicked()) {
                $candidacy->setBiography(null);
            }

            if ($manager->candidateInCommittee($candidacy, $adherent, $committee)) {
                $this->addFlash('info', 'Votre candidature a bien été enregistrée');

                return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
            }
        }

        return $this->render('committee/edit_candidacy.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/modifie-ma-candidature", name="app_committee_update_candidacy", methods={"GET", "POST"})
     *
     * @Security("is_granted('MEMBER_OF_COMMITTEE', committee)")
     */
    public function updateCandidacyAction(
        Request $request,
        Committee $committee,
        UserInterface $adherent,
        CommitteeManager $manager,
        ImageManager $imageManager
    ): Response {
        $this->disableInProduction();

        if (!$committee->getCommitteeElection() || !$committee->getCommitteeElection()->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez plus modifier votre candidature.');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        /** @var Adherent $adherent */
        if (!$candidacy = $manager->getCandidacy($adherent, $committee)) {
            throw $this->createNotFoundException();
        }

        $form = $this
            ->createForm(CandidacyBiographyType::class, $candidacy)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($candidacy->isRemoveImage()) {
                if ($candidacy->hasImageName()) {
                    $imageManager->removeImage($candidacy);
                }
            } elseif ($candidacy->getImage()) {
                $imageManager->saveImage($candidacy);
            }

            $manager->saveCandidacy($candidacy);

            $this->addFlash('info', 'Votre candidature a bien été modifiée');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/edit_candidacy.html.twig', [
            'candidacy' => $candidacy,
            'committee' => $committee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/retirer-sa-candidature", name="app_committee_remove_candidacy", methods={"GET"})
     *
     * @Security("is_granted('MEMBER_OF_COMMITTEE', committee)")
     */
    public function removeCandidacy(Request $request, Committee $committee, CommitteeManager $manager): Response
    {
        $this->disableInProduction();

        if (!$committee->getCommitteeElection() || !$committee->getCommitteeElection()->isCandidacyPeriodActive()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer votre candidature.');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        $manager->removeCandidacy($this->getUser(), $committee);

        $this->addFlash('info', 'Votre candidature a bien été supprimée');

        if ($request->query->has('back')) {
            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->redirectToRoute('app_adherent_committees');
    }

    /**
     * @param CommitteeFeedItem[]|iterable $feeds
     */
    private function createTimelineDeleteForms(iterable $feeds): array
    {
        $forms = [];
        foreach ($feeds as $feed) {
            if ($this->isGranted('ADMIN_FEED_COMMITTEE', $feed)) {
                $forms[$feed->getId()] = $this->createDeleteForm(
                    $this->generateUrl('app_committee_timeline_delete',
                        [
                            'id' => $feed->getId(),
                            'slug' => $feed->getCommittee()->getSlug(),
                        ]),
                    'committee_feed_delete'
                )->createView()
                ;
            }
        }

        return $forms;
    }

    private function getCommitteeManager(): CommitteeManager
    {
        return $this->get(CommitteeManager::class);
    }
}
