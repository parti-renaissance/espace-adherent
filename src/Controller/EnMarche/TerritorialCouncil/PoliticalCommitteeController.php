<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/comite-politique", name="app_political_committee_")
 *
 * @Security("is_granted('POLITICAL_COMMITTEE_MEMBER')")
 */
class PoliticalCommitteeController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function indexAction(
        UserInterface $adherent,
        PoliticalCommitteeFeedItemRepository $feedItemRepository
    ): Response {
        $feedItems = $feedItemRepository->getFeedItems(
            $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee(),
            $this->getParameter('timeline_max_messages')
        );

        return $this->render('territorial_council/political_committee/index.html.twig', [
            'feed_items' => $feedItems,
            'max_feed_items' => $this->getParameter('timeline_max_messages'),
        ]);
    }

    /**
     * @Route("/{uuid}/messages", name="messages", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     */
    public function messagesAction(
        Request $request,
        PoliticalCommittee $politicalCommittee,
        PoliticalCommitteeFeedItemRepository $feedItemRepository
    ): Response {
        $feedItems = $feedItemRepository->getFeedItems(
            $politicalCommittee,
            $this->getParameter('timeline_max_messages'),
            $request->query->getInt('offset', 0)
        );

        return $this->render('territorial_council/partials/_feed_items.html.twig', [
            'feed_items' => $feedItems,
            'max_feed_items' => $this->getParameter('timeline_max_messages'),
        ]);
    }
}
