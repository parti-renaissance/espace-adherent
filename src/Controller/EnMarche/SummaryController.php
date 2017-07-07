<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Summary;
use AppBundle\Membership\MemberActivityTracker;
use AppBundle\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/membre/{slug}")
 */
class SummaryController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="app_summary_index")
     * @Method("GET")
     * @Entity("summary", expr="repository.findOneBySlug(slug)")
     */
    public function indexAction(Summary $summary): Response
    {
        $this->disableInProduction();
        $this->get(SummaryManager::class)->setUrlProfilePicture($summary);

        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $this->get(MemberActivityTracker::class)->getRecentActivitiesForAdherent($summary->getMember()),
        ]);
    }
}
