<?php

namespace App\Controller\EnMarche;

use App\Entity\Summary;
use App\Membership\MemberActivityTracker;
use App\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/membre/{slug}")
 */
class SummaryController extends Controller
{
    /**
     * @Route(name="app_summary_index", methods={"GET"})
     * @Entity("summary", expr="repository.findOneBySlug(slug)")
     */
    public function indexAction(Summary $summary): Response
    {
        $this->get(SummaryManager::class)->setUrlProfilePicture($summary);

        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $this->get(MemberActivityTracker::class)->getRecentActivitiesForAdherent($summary->getMember()),
        ]);
    }
}
