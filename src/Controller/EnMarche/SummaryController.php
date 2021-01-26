<?php

namespace App\Controller\EnMarche;

use App\Entity\Summary;
use App\Membership\MemberActivityTracker;
use App\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/membre/{slug}")
 */
class SummaryController extends AbstractController
{
    /**
     * @Route(name="app_summary_index", methods={"GET"})
     * @Entity("summary", expr="repository.findOneBySlug(slug)")
     */
    public function indexAction(Summary $summary, SummaryManager $manager, MemberActivityTracker $tracker): Response
    {
        $manager->setUrlProfilePicture($summary);

        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $tracker->getRecentActivitiesForAdherent($summary->getMember()),
        ]);
    }
}
