<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Exception\SitemapException;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Sitemap\SitemapFactory;
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
    public function indexAction(): Response
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
    public function sitemapIndexAction(): Response
    {
        return $this->createXmlResponse($this->get(SitemapFactory::class)->createSitemapIndex());
    }

    /**
     * @Route(
     *     "/sitemap_{type}_{page}.xml",
     *     requirements={"type"=AppBundle\Sitemap\SitemapFactory::ALL_TYPES, "page"="\d+"},
     *     defaults={"page"="1", "_enable_campaign_silence"=true},
     *     name="app_sitemap"
     * )
     * @Method("GET")
     */
    public function sitemapAction(string $type, int $page): Response
    {
        try {
            return $this->createXmlResponse($this->get(SitemapFactory::class)->createSitemap($type, $page));
        } catch (SitemapException $exception) {
            return $this->redirectToRoute('app_sitemap_index', [], Response::HTTP_MOVED_PERMANENTLY);
        }
    }

    private function createXmlResponse(string $content): Response
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
