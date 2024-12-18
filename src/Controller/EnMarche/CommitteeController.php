<?php

namespace App\Controller\EnMarche;

use App\Committee\CommitteeManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Form\CommitteeFeedItemMessageType;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\Security\Voter\Committee\ChangeCommitteeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route(path: '/comites/{slug}')]
class CommitteeController extends AbstractController
{
    public function __construct(
        private readonly CommitteeManager $committeeManager,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly int $timelineMaxItems,
    ) {
    }

    #[IsGranted('SHOW_COMMITTEE', subject: 'committee')]
    #[Route(name: 'app_committee_show', methods: ['GET'])]
    public function showAction(
        Request $request,
        Committee $committee,
        AnonymousFollowerSession $anonymousFollowerSession,
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
            'committee_timeline_max_messages' => $this->timelineMaxItems,
        ]);
    }

    #[IsGranted('ADMIN_FEED_COMMITTEE', subject: 'committeeFeedItem')]
    #[ParamConverter('committee', options: ['mapping' => ['slug' => 'slug']])]
    #[ParamConverter('committeeFeedItem', options: ['mapping' => ['id' => 'id']])]
    #[Route(path: '/timeline/{id}/modifier', name: 'app_committee_timeline_edit', methods: ['GET', 'POST'])]
    public function timelineEditAction(
        EntityManagerInterface $manager,
        Request $request,
        Committee $committee,
        CommitteeFeedItem $committeeFeedItem,
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

    #[IsGranted('SHOW_COMMITTEE', subject: 'committee')]
    #[Route(path: '/timeline', name: 'app_committee_timeline', methods: ['GET'])]
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
            'has_role_adherent' => $this->getUser() instanceof Adherent && $this->getUser()->isAdherent(),
            'has_role_user' => $this->isGranted('ROLE_USER'),
        ]);
    }

    #[IsGranted('FOLLOW_COMMITTEE', subject: 'committee')]
    #[Route(path: '/rejoindre', name: 'app_committee_follow', condition: "request.request.has('token')", methods: ['POST'])]
    public function followAction(
        Request $request,
        Committee $committee,
        CommitteeMembershipManager $committeeMembershipManager,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$this->isCsrfTokenValid('committee.follow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to follow committee.');
        }

        $committeeMembershipManager->followCommittee($this->getUser(), $committee, CommitteeMembershipTriggerEnum::MANUAL);

        return new JsonResponse([
            'button' => [
                'label' => 'Quitter ce comité',
                'action' => 'quitter',
                'csrf_token' => (string) $csrfTokenManager->getToken('committee.unfollow'),
            ],
        ]);
    }

    #[IsGranted('UNFOLLOW_COMMITTEE', subject: 'committee')]
    #[Route(path: '/quitter', name: 'app_committee_unfollow', condition: "request.request.has('token')", methods: ['POST'])]
    public function unfollowAction(
        Request $request,
        Committee $committee,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$this->isCsrfTokenValid('committee.unfollow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unfollow committee.');
        }

        $this->committeeMembershipManager->unfollowCommittee($this->getUser()->getMembershipFor($committee));

        return new JsonResponse([
            'button' => [
                'label' => 'Suivre ce comité',
                'action' => 'rejoindre',
                'csrf_token' => (string) $csrfTokenManager->getToken('committee.unfollow'),
            ],
        ]);
    }

    #[IsGranted(ChangeCommitteeVoter::PERMISSION)]
    #[Route(path: '/voter', defaults: ['enable' => true], name: 'app_committee_vote', condition: 'request.isXmlHttpRequest()', methods: ['POST'])]
    #[Route(path: '/ne-plus-voter', defaults: ['enable' => false], name: 'app_committee_unvote', condition: 'request.isXmlHttpRequest()', methods: ['POST'])]
    public function toggleCommitteeVoteAction(
        bool $enable,
        Request $request,
        Committee $committee,
        MessageBusInterface $bus,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

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
}
