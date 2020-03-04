<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Exception\CommitteeMembershipException;
use AppBundle\Form\CommitteeFeedItemMessageType;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/voter", name="app_committee_vote", condition="request.request.has('token')", methods={"POST"})
     */
    public function voteAction(Request $request, Committee $committee, CommitteeManager $manager): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('committee.vote', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to vote committee.');
        }

        try {
            $membership = $manager->enableVoteInCommittee($this->getUser(), $committee);
        } catch (CommitteeMembershipException $e) {
            throw new \BadRequestException($e->getMessage());
        }

        if (!$membership) {
            throw $this->createNotFoundException("Vous n'êtes pas membre de ce comité.");
        }

        return new JsonResponse('OK', 200);
    }

    /**
     * @Route("/ne-plus-voter", name="app_committee_unvote", condition="request.request.has('token')", methods={"POST"})
     */
    public function unvoteAction(Request $request, Committee $committee, CommitteeManager $manager): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('committee.vote', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to vote committee.');
        }

        try {
            $membership = $manager->disableVoteInCommittee($this->getUser(), $committee);
        } catch (CommitteeMembershipException $e) {
            throw new \BadRequestException($e->getMessage());
        }

        if (!$membership) {
            throw $this->createNotFoundException("Vous n'êtes pas membre de ce comité.");
        }

        return new JsonResponse('OK', 200);
    }

    /**
     * @Route("/candidater", name="app_committee_candidate", condition="request.request.has('token')", methods={"POST"})
     */
    public function candidateAction(Request $request, Committee $committee, CommitteeManager $manager): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('committee.candidate', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to candidate in committee.');
        }

        $membership = $manager->candidateInCommittee($this->getUser(), $committee);
        if (!$membership) {
            throw $this->createNotFoundException('Vous ne votez pas dans ce comité.');
        }

        return new JsonResponse('OK', 200);
    }

    /**
     * @Route("/retirer-sa-candidature", name="app_committee_remove_candidacy", condition="request.request.has('token')", methods={"POST"})
     */
    public function removeCandidacy(Request $request, Committee $committee, CommitteeManager $manager): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('committee.uncandidate', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to candidate in committee.');
        }

        $membership = $manager->removeCandidacy($this->getUser(), $committee);
        if (!$membership) {
            throw $this->createNotFoundException('Vous ne votez pas dans ce comité.');
        }

        return new JsonResponse('OK', 200);
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
