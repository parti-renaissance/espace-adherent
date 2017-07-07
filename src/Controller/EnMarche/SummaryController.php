<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Summary;
use AppBundle\Membership\MemberActivityTracker;
use League\Glide\Signatures\SignatureFactory;
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

        $pathImage = 'images/'.$summary->getMemberUuid().'.jpg';
        $cache = substr(md5((new \DateTime())->format('U')), 0, 20);
        $signature = SignatureFactory::create($this->getParameter('kernel.secret'))->generateSignature($pathImage, ['cache' => $cache]);

        return $this->render('summary/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $this->get(MemberActivityTracker::class)->getRecentActivitiesForAdherent($summary->getMember()),
            'url_photo' => $this->generateUrl('asset_url', [
                'path' => $pathImage,
                's' => $signature,
                'cache' => $cache,
            ]),
        ]);
    }
}
