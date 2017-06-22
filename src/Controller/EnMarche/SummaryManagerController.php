<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/espace-adherent/mon-cv")
 */
class SummaryManagerController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="app_summary_manager_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $this->disableInProduction();

        return $this->render('summary_manager/index.html.twig', [
            'summary' => $this->get(SummaryManager::class)->getForAdherent($this->getUser()),
            'recent_activities' => [], // TODO $this->get(MembershipTracker::class)->getRecentActivitiesForAdherent($this->getUser()),
        ]);
    }
}
