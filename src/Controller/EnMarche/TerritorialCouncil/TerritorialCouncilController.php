<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Controller\EnMarche\FeedItemControllerTrait;
use App\Controller\EntityControllerTrait;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\FeedItem\FeedItemTypeEnum;
use App\Form\FeedItemType;
use App\Repository\TerritorialCouncil\CandidacyRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository;
use App\Security\Voter\TerritorialCouncil\AccessVoter;
use App\Security\Voter\TerritorialCouncil\ManageTerritorialCouncilVoter;
use App\TerritorialCouncil\ElectionPoll\Manager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/conseil-territorial", name="app_territorial_council_")
 *
 * @Security("is_granted('ROLE_ADHERENT')")
 */
class TerritorialCouncilController extends Controller
{
    use CanaryControllerTrait;
    use FeedItemControllerTrait;
    use EntityControllerTrait;

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     * @Route("/{uuid}/faq", name="selected_faq", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     */
    public function faqAction(UserInterface $adherent, TerritorialCouncil $territorialCouncil = null): Response
    {
        $this->checkAccess($territorialCouncil);

        if (!$withSelectedCouncil = null !== $territorialCouncil) {
            $membership = $adherent->getTerritorialCouncilMembership();
            $territorialCouncil = $membership->getTerritorialCouncil();
        }

        return $this->render('territorial_council/faq.html.twig', [
            'membership' => $membership ?? null,
            'territorial_council' => $territorialCouncil,
            'with_selected_council' => $withSelectedCouncil,
        ]);
    }

    /**
     * @Route("/membres", name="members", methods={"GET"})
     * @Route("/{uuid}/membres", name="selected_members", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     */
    public function listMembersAction(UserInterface $adherent, TerritorialCouncil $territorialCouncil = null): Response
    {
        $this->disableInProduction();

        $this->checkAccess($territorialCouncil);

        if (!$withSelectedCouncil = null !== $territorialCouncil) {
            $membership = $adherent->getTerritorialCouncilMembership();
            $territorialCouncil = $membership->getTerritorialCouncil();
        }

        return $this->render('territorial_council/members.html.twig', [
            'membership' => $membership ?? null,
            'territorial_council' => $territorialCouncil,
            'with_selected_council' => $withSelectedCouncil,
        ]);
    }

    /**
     * @Route("/liste-candidature", name="candidacy_list", methods={"GET"})
     * @Route("/{uuid}/liste-candidature", name="selected_candidacy_list", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     *
     * @param Adherent $adherent
     */
    public function candidacyListAction(
        UserInterface $adherent,
        CandidacyRepository $repository,
        TerritorialCouncil $territorialCouncil = null
    ): Response {
        $this->checkAccess($territorialCouncil);

        if (!$withSelectedCouncil = null !== $territorialCouncil) {
            $membership = $adherent->getTerritorialCouncilMembership();
            $territorialCouncil = $membership->getTerritorialCouncil();
        }

        if (!$election = $territorialCouncil->getCurrentElection()) {
            return $withSelectedCouncil ?
                $this->redirectToRoute('app_territorial_council_selected_index', ['uuid' => $territorialCouncil->getUuid()])
                : $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/candidacy_list.html.twig', [
            'candidacies' => $repository->findAllConfirmedForElection($election),
            'election' => $election,
            'membership' => $membership ?? null,
            'territorial_council' => $territorialCouncil,
            'with_selected_council' => $withSelectedCouncil,
        ]);
    }

    /**
     * @Route("/{uuid}/sondage", name="election_poll_save_vote", methods={"POST"}, requirements={"uuid": "%pattern_uuid%"})
     *
     * @param Adherent $adherent
     */
    public function electionPollAction(
        Request $request,
        Poll $electionPoll,
        UserInterface $adherent,
        Manager $voteManager
    ): Response {
        $this->checkAccess();

        if (!$electionPoll->getElection()->isCandidacyPeriodActive()) {
            return $this->redirectToRoute('app_territorial_council_index');
        }

        if ($voteManager->hasVoted($electionPoll, $membership = $adherent->getTerritorialCouncilMembership())) {
            $this->addFlash('error', 'Vous avez déjà participé à ce sondage.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        if (!($choiceUuid = $request->request->get('poll-choice')) || !Uuid::isValid($choiceUuid) || !($choice = $voteManager->findChoice($choiceUuid))) {
            $this->addFlash('error', 'Choix est invalide.');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        $voteManager->vote($choice, $membership);

        $this->addFlash('info', 'Votre participation au sondage a bien été enregistrée.');

        return $this->redirectToRoute('app_territorial_council_index');
    }

    /**
     * @Route("/accueil", name="homepage", methods={"GET"})
     * @Route("/accueil/{uuid}", name="selected_homepage", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     *
     * @param Adherent $adherent
     */
    public function homepageAction(UserInterface $adherent, TerritorialCouncil $territorialCouncil = null): Response
    {
        $this->checkAccess($territorialCouncil);

        if (!$withSelectedCouncil = null !== $territorialCouncil) {
            $membership = $adherent->getTerritorialCouncilMembership();
            $territorialCouncil = $membership->getTerritorialCouncil();
        }

        return $this->render('territorial_council/homepage.html.twig', [
            'membership' => $membership ?? null,
            'territorial_council' => $territorialCouncil,
            'with_selected_council' => $withSelectedCouncil,
        ]);
    }

    /**
     * @Route("", name="index", methods={"GET"})
     * @Route("/{uuid}", name="selected_index", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
     */
    public function feedItemsAction(
        Request $request,
        UserInterface $adherent,
        TerritorialCouncilFeedItemRepository $feedItemRepository,
        TerritorialCouncil $territorialCouncil = null
    ): Response {
        $this->checkAccess($territorialCouncil);

        if (!$withSelectedCouncil = null !== $territorialCouncil) {
            $membership = $adherent->getTerritorialCouncilMembership();
            $territorialCouncil = $membership->getTerritorialCouncil();
        }

        $page = $request->query->getInt('page', 1);
        $feedItems = $feedItemRepository->getFeedItems(
            $territorialCouncil,
            $page,
            $this->getParameter('timeline_max_messages')
        );

        if (1 < $page) {
            return $this->render('territorial_council/partials/_feed_items.html.twig', [
                'feed_items' => $feedItems,
                'feed_items_forms' => $this->createFeedItemDeleteForms($feedItems, FeedItemTypeEnum::TERRITORIAL_COUNCIL),
                'feed_item_type' => FeedItemTypeEnum::TERRITORIAL_COUNCIL,
            ]);
        }

        return $this->render('territorial_council/messages.html.twig', [
            'membership' => $membership ?? null,
            'territorial_council' => $territorialCouncil,
            'with_selected_council' => $withSelectedCouncil,
            'feed_items' => $feedItems,
            'feed_items_forms' => $this->createFeedItemDeleteForms($feedItems, FeedItemTypeEnum::TERRITORIAL_COUNCIL),
            'feed_item_type' => FeedItemTypeEnum::TERRITORIAL_COUNCIL,
        ]);
    }

    private function checkAccess(TerritorialCouncil $territorialCouncil = null): void
    {
        if ($territorialCouncil) {
            $this->denyAccessUnlessGranted(ManageTerritorialCouncilVoter::PERMISSION, $territorialCouncil);
        } else {
            $this->denyAccessUnlessGranted(AccessVoter::PERMISSION);
        }
    }

    /**
     * @Route("/messages/{id}/modifier", name="edit_feed_item", methods={"GET", "POST"})
     * @Security("is_granted('CAN_MANAGE_FEED_ITEM', feedItem)")
     */
    public function feedItemEditAction(Request $request, TerritorialCouncilFeedItem $feedItem): Response
    {
        $form = $this
            ->createForm(FeedItemType::class, $feedItem)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'common.message_edited');

            return $this->redirectToRoute('app_territorial_council_index');
        }

        return $this->render('territorial_council/edit_feed_item.html.twig', [
            'base_layout' => 'territorial_council/_main_layout.html.twig',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/messages/{id}/supprimer", name="delete_feed_item", methods={"DELETE"})
     * @Security("is_granted('CAN_MANAGE_FEED_ITEM', feedItem)")
     */
    public function deleteFeedItemAction(Request $request, TerritorialCouncilFeedItem $feedItem): Response
    {
        $this->deleteFeedItem($request, $feedItem, FeedItemTypeEnum::TERRITORIAL_COUNCIL);

        return $this->redirectToRoute('app_territorial_council_index');
    }
}
