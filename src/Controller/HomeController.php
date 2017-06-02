<?php

namespace AppBundle\Controller;

use AppBundle\Form\NewsletterSubscriptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="homepage")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig', [
            'blocks' => $this->getDoctrine()->getRepository('AppBundle:HomeBlock')->findHomeBlocks(),
            'live_links' => $this->getDoctrine()->getRepository('AppBundle:LiveLink')->findHomeLiveLinks(),
            'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class)->createView(),
        ]);
    }

    /**
     * @Route("/sitemap.xml", defaults={"_enable_campaign_silence"=true}, name="app_sitemap_index")
     * @Method("GET")
     */
    public function sitemapIndexAction()
    {
        return $this->createXmlResponse($this->get('app.content.sitemap_factory')->createSitemapIndex());
    }

    /**
     * @Route(
     *     "/sitemap_{type}_{page}.xml",
     *     requirements={"type"="main|content|committees|events", "page"="\d+"},
     *     defaults={"page"="1", "_enable_campaign_silence"=true},
     *     name="app_sitemap"
     * )
     * @Method("GET")
     */
    public function sitemapAction($type, $page): Response
    {
        return $this->createXmlResponse($this->get('app.content.sitemap_factory')->createSitemap($type, (int) $page));
    }

    private function createXmlResponse(string $content)
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
