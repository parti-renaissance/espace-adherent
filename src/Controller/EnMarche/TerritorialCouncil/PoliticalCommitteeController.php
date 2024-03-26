<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use App\Controller\EnMarche\FeedItemControllerTrait;
use App\Controller\EntityControllerTrait;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\FeedItem\FeedItemTypeEnum;
use App\Form\FeedItemType;
use App\Repository\TerritorialCouncil\OfficialReportRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/comite-politique', name: 'app_political_committee_')]
#[IsGranted('POLITICAL_COMMITTEE_MEMBER')]
class PoliticalCommitteeController extends AbstractController
{
    use CanaryControllerTrait;
    use FeedItemControllerTrait;
    use EntityControllerTrait;

    private $timelineMaxItems;

    public function __construct(int $timelineMaxItems)
    {
        $this->timelineMaxItems = $timelineMaxItems;
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function feedItemsAction(
        Request $request,
        PoliticalCommitteeFeedItemRepository $feedItemRepository
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $page = $request->query->getInt('page', 1);

        $membership = $adherent->getPoliticalCommitteeMembership();
        $politicalCommittee = $membership->getPoliticalCommittee();
        $feedItems = $feedItemRepository->getFeedItems($politicalCommittee, $page, $this->timelineMaxItems);

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

    #[Route(path: '/messages/{id}/modifier', name: 'edit_feed_item', methods: ['GET', 'POST'])]
    #[IsGranted('CAN_MANAGE_FEED_ITEM', subject: 'feedItem')]
    public function feedItemEditAction(
        EntityManagerInterface $manager,
        Request $request,
        PoliticalCommitteeFeedItem $feedItem
    ): Response {
        $form = $this
            ->createForm(FeedItemType::class, $feedItem)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('info', 'common.message_edited');

            return $this->redirectToRoute('app_political_committee_index');
        }

        return $this->render('territorial_council/edit_feed_item.html.twig', [
            'base_layout' => 'territorial_council/political_committee/_layout.html.twig',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/messages/{id}/supprimer', name: 'delete_feed_item', methods: ['DELETE'])]
    #[IsGranted('CAN_MANAGE_FEED_ITEM', subject: 'feedItem')]
    public function deleteFeedItemAction(
        EntityManagerInterface $em,
        Request $request,
        PoliticalCommitteeFeedItem $feedItem
    ): Response {
        $this->deleteFeedItem($em, $request, $feedItem, FeedItemTypeEnum::POLITICAL_COMMITTEE);

        return $this->redirectToRoute('app_political_committee_index');
    }

    #[Route(path: '/proces-verbaux', name: 'official_report_list', methods: ['GET'])]
    public function listOfficialReportsAction(OfficialReportRepository $reportRepository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $reports = $reportRepository->getReportsForPoliticalCommittee(
            $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee()
        );

        return $this->render('territorial_council/political_committee/official_reports.html.twig', [
            'reports' => $reports,
        ]);
    }

    #[Route(path: '/proces-verbaux/{uuid}', name: 'official_report_download', methods: ['GET'])]
    #[IsGranted('CAN_DOWNLOAD_OFFICIAL_REPORT', subject: 'officialReport')]
    public function downloadReportAction(OfficialReport $officialReport, FilesystemOperator $defaultStorage): Response
    {
        $lastDocument = $officialReport->getLastDocument();
        $filePath = $lastDocument->getFilePathWithDirectory();

        if (!$defaultStorage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this Official report.');
        }

        $response = new Response($defaultStorage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $lastDocument->getMimeType(),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $lastDocument->getFilenameForDownload()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
