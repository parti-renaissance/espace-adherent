<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Summary;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
    public function indexAction(Summary $summary)
    {
        $this->disableInProduction();

        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => [], // TODO $this->get(MembershipTracker::class)->getRecentActivitiesForAdherent($this->getUser()),
        ]);
    }
}
