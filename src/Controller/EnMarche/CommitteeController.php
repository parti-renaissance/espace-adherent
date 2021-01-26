<?php

namespace App\Controller\EnMarche;

use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Controller\EntityControllerTrait;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Form\CommitteeFeedItemMessageType;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/comites/{slug}")
 */
class CommitteeController extends AbstractController
{
    use EntityControllerTrait;

    private $committeeManager;
    private $timelineMaxItems;

    public function __construct(CommitteeManager $committeeManager, int $timelineMaxItems)
    {
        $this->committeeManager = $committeeManager;
        $this->timelineMaxItems = $timelineMaxItems;
    }

    /**
     * @Route(name="app_committee_show", methods={"GET"})
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function showAction(
        Request $request,
        Committee $committee,
        AnonymousFollowerSession $anonymousFollowerSession
    ): Response {
        if ($this->isGranted('IS_ANONYMOUS') && $authenticate = $anonymousFollowerSession->start($request)) {
            return $authenticate;
        }

        if ($committee->isPending() || $committee->isPreApproved()) {
            return $this->redirectToRoute('app_committee_manager_edit', [
                'slug' => $committee->getSlug(),
            ]);
        }

        $feeds = $this->committeeManager->getTimeline($committee, $this->timelineMaxItems);

        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->committeeManager->getCommitteeHosts($committee),
            'committee_timeline' => $feeds,
            'committee_timeline_forms' => $this->createTimelineDeleteForms($feeds),
            'committee_timeline_max_messages' => $this->timelineMaxItems,
        ]);
    }

    /**
     * @Route("/timeline/{id}/modifier", name="app_committee_timeline_edit", methods={"GET", "POST"})
     * @ParamConverter("committee", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("committeeFeedItem", options={"mapping": {"id": "id"}})
     * @Security("is_granted('ADMIN_FEED_COMMITTEE', committeeFeedItem)")
     */
    public function timelineEditAction(
        EntityManagerInterface $manager,
        Request $request,
        Committee $committee,
        CommitteeFeedItem $committeeFeedItem
    ): Response {
        $form = $this
            ->createForm(CommitteeFeedItemMessageType::class, $committeeFeedItem)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('info', 'common.message_edited');

            return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
        }

        return $this->render('committee/timeline/edit.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->committeeManager->getCommitteeHosts($committee),
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
        EntityManagerInterface $em,
        Request $request,
        Committee $committee,
        CommitteeFeedItem $committeeFeedItem
    ): Response {
        $form = $this->createDeleteForm('', 'committee_feed_delete', $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        $em->remove($committeeFeedItem);
        $em->flush();

        $this->addFlash('info', 'common.message_deleted');

        return $this->redirectToRoute('app_committee_show', ['slug' => $committee->getSlug()]);
    }

    /**
     * @Route("/timeline", name="app_committee_timeline", methods={"GET"})
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function timelineAction(Request $request, Committee $committee): Response
    {
        $timeline = $this->committeeManager->getTimeline(
            $committee,
            $this->timelineMaxItems,
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
    public function followAction(
        Request $request,
        Committee $committee,
        CommitteeManagementAuthority $committeeManagementAuthority,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if (!$this->isCsrfTokenValid('committee.follow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to follow committee.');
        }

        $committeeManagementAuthority->followCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Quitter ce comité',
                'action' => 'quitter',
                'csrf_token' => (string) $csrfTokenManager->getToken('committee.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/quitter", name="app_committee_unfollow", condition="request.request.has('token')", methods={"POST"})
     * @Security("is_granted('UNFOLLOW_COMMITTEE', committee)")
     */
    public function unfollowAction(
        Request $request,
        Committee $committee,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if (!$this->isCsrfTokenValid('committee.unfollow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unfollow committee.');
        }

        $this->committeeManager->unfollowCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Suivre ce comité',
                'action' => 'rejoindre',
                'csrf_token' => (string) $csrfTokenManager->getToken('committee.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/voter", defaults={"enable": true}, name="app_committee_vote", condition="request.isXmlHttpRequest()", methods={"POST"})
     * @Route("/ne-plus-voter", defaults={"enable": false}, name="app_committee_unvote", condition="request.isXmlHttpRequest()", methods={"POST"})
     *
     * @Security("is_granted('ABLE_TO_CHANGE_COMMITTEE_VOTE') and is_granted('COMMITTEE_IS_NOT_LOCKED', committee)")
     *
     * @param Adherent|UserInterface $adherent
     */
    public function toggleCommitteeVoteAction(
        bool $enable,
        UserInterface $adherent,
        Request $request,
        Committee $committee,
        MessageBusInterface $bus
    ): Response {
        if (!$this->isCsrfTokenValid('committee.vote', $request->request->get('token'))) {
            return $this->json([
                'status' => 'NOK',
                'error' => 'Requête invalide',
            ], Response::HTTP_BAD_REQUEST);
        }

        $membership = $adherent->getMembershipFor($committee);

        if ($enable) {
            $this->committeeManager->enableVoteInMembership($membership, $adherent);
        } else {
            $this->committeeManager->disableVoteInMembership($membership);
        }

        $bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));

        return $this->json(['status' => 'OK'], Response::HTTP_OK);
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
}
