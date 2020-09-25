<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Controller\EnMarche\FeedItemControllerTrait;
use App\Controller\EntityControllerTrait;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\FeedItem\FeedItemTypeEnum;
use App\Form\FeedItemType;
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
    use FeedItemControllerTrait;
    use EntityControllerTrait;

    /**
     * @Route("/messages", name="messages", methods={"GET"})
     */
    public function feedItemsAction(
        Request $request,
        UserInterface $adherent,
        PoliticalCommitteeFeedItemRepository $feedItemRepository
    ): Response {
        $page = $request->query->getInt('page', 1);

        $membership = $adherent->getPoliticalCommitteeMembership();
        $politicalCommittee = $membership->getPoliticalCommittee();
        $feedItems = $feedItemRepository->getFeedItems(
            $politicalCommittee,
            $page,
            $this->getParameter('timeline_max_messages')
        );

        if (1 < $page) {
            return $this->render('territorial_council/partials/_feed_items.html.twig', [
                'feed_items' => $feedItems,
                'feed_items_forms' => $this->createFeedItemDeleteForms($feedItems, FeedItemTypeEnum::POLITICAL_COMMITTEE),
                'feed_item_type' => FeedItemTypeEnum::POLITICAL_COMMITTEE,
            ]);
        }

        return $this->render('territorial_council/political_committee/messages.html.twig', [
            'feed_items' => $feedItems,
            'feed_items_forms' => $this->createFeedItemDeleteForms($feedItems, FeedItemTypeEnum::POLITICAL_COMMITTEE),
            'feed_item_type' => FeedItemTypeEnum::POLITICAL_COMMITTEE,
        ]);
    }

    /**
     * @Route("/messages/{id}/modifier", name="edit_feed_item", methods={"GET", "POST"})
     * @Security("is_granted('CAN_MANAGE_FEED_ITEM', feedItem)")
     */
    public function feedItemEditAction(Request $request, PoliticalCommitteeFeedItem $feedItem): Response
    {
        $form = $this
            ->createForm(FeedItemType::class, $feedItem)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'common.message_edited');

            return $this->redirectToRoute('app_political_committee_messages');
        }

        return $this->render('territorial_council/edit_feed_item.html.twig', [
            'base_layout' => 'territorial_council/political_committee/_layout.html.twig',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/messages/{id}/supprimer", name="delete_feed_item", methods={"DELETE"})
     * @Security("is_granted('CAN_MANAGE_FEED_ITEM', feedItem)")
     */
    public function deleteFeedItemAction(Request $request, PoliticalCommitteeFeedItem $feedItem): Response
    {
        $this->deleteFeedItem($request, $feedItem, FeedItemTypeEnum::POLITICAL_COMMITTEE);

        return $this->redirectToRoute('app_political_committee_messages');
    }
}
