<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\FacebookVideo;
use AppBundle\Entity\Page;
use AppBundle\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Each time you add or update a custom url with an harcorded slug in the controller code, you must update the
 * AppBundle\Entity\Page::URLS constant and reindex algolia's page index.
 */
class PageController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/campus/mooc", defaults={"_enable_campaign_silence"=true}, name="page_mooc")
     * @Method("GET")
     */
    public function moocAction()
    {
        return $this->render('page/campus/mooc.html.twig');
    }

    /**
     * @Route("/campus", defaults={"_enable_campaign_silence"=true}, name="page_campus")
     * @Method("GET")
     */
    public function campusAction()
    {
        return $this->render('page/campus/home.html.twig');
    }

    /**
     * @Route("/campus/dificultes-internet", defaults={"_enable_campaign_silence"=true}, name="page_campus_internet")
     * @Method("GET")
     */
    public function campusInternetAction()
    {
        return $this->render('page/campus/internet.html.twig');
    }

    /**
     * @Route("/emmanuel-macron/videos", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_videos")
     * @Method("GET")
     */
    public function emmanuelMacronVideosAction()
    {
        return $this->render('page/emmanuel-macron/videos.html.twig', [
            'videos' => $this->getDoctrine()->getRepository(FacebookVideo::class)->findPublishedVideos(),
        ]);
    }

    /**
     * @Route("/le-mouvement/legislatives", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement_legislatives")
     * @Method("GET")
     */
    public function mouvementLegislativesAction()
    {
        return $this->redirect('https://legislatives.en-marche.fr', Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/okcandidatlegislatives", name="legislatives_confirm_newsletter")
     * @Method("GET")
     */
    public function legislativesConfirmNewsletterAction()
    {
        return $this->render('legislative_candidate/confirm_newsletter.html.twig');
    }

    /**
     * @Route("/elles-marchent", defaults={"_enable_campaign_silence"=true}, name="page_elles_marchent")
     * @Method("GET")
     */
    public function ellesMarchentAction()
    {
        return $this->render('page/elles-marchent.html.twig');
    }

    /**
     * @Route("/candidatures-delegue-general-et-bureau-executif", defaults={"_enable_campaign_silence"=true}, name="page_burex")
     * @Method("GET")
     */
    public function burexAction()
    {
        return $this->render('page/burex-lists.html.twig');
    }

    /**
     * @Entity("page", expr="repository.findOneBySlug(slug)")
     */
    public function showPageAction(Request $request, Page $page)
    {
        $template = 'page/layout.html.twig';

        switch ($request->query->get('slug')) {
            case 'le-mouvement/notre-organisation':
                $template = 'page/le-mouvement/notre-organisation.html.twig';

                break;
            case 'politique-cookies':
                $template = 'page/politique-cookies.html.twig';

                break;
            case 'action-talents':
                $template = 'page/action-talents/home.html.twig';

                break;
            case 'action-talents/candidater':
                $template = 'page/action-talents/apply.html.twig';

                break;
        }

        return $this->render($template, ['page' => $page]);
    }
}
