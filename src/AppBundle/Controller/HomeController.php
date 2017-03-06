<?php

namespace AppBundle\Controller;

use AppBundle\Form\NewsletterSubscriptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tackk\Cartographer\SitemapIndex;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
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
     * @Route("/health", name="health")
     * @Method("GET")
     */
    public function healthAction()
    {
        return new Response('Healthy');
    }

    /**
     * @Route("/sitemap.xml", name="app_sitemap_index")
     * @Method("GET")
     */
    public function sitemapIndexAction()
    {
        $sitemapIndex = new SitemapIndex();

        foreach (['main', 'content', 'committees', 'events'] as $type) {
            $sitemapIndex->add(
                $this->generateUrl('app_sitemap', ['type' => $type], UrlGeneratorInterface::ABSOLUTE_URL),
                null
            );
        }

        return $this->createXmlResponse((string) $sitemapIndex);
    }

    /**
     * @Route(
     *     "/sitemap_{type}.xml",
     *     requirements={"type"="main|content|committees|events"},
     *     name="app_sitemap"
     * )
     * @Method("GET")
     */
    public function sitemapAction($type): Response
    {
        $sitemap = '';

        if ('main' === $type) {
            $sitemap = $this->get('app.content.sitemap_factory')->createMainSitemap();
        } elseif ('content' === $type) {
            $sitemap = $this->get('app.content.sitemap_factory')->createContentSitemap();
        } elseif ('committees' === $type) {
            $sitemap = $this->get('app.content.sitemap_factory')->createCommitteesSitemap();
        } elseif ('events' === $type) {
            $sitemap = $this->get('app.content.sitemap_factory')->createEventsSitemap();
        }

        return $this->createXmlResponse($sitemap);
    }

    private function createXmlResponse(string $content)
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
