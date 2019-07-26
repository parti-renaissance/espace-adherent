<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\HomeBlock;
use AppBundle\Entity\LiveLink;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Exception\SitemapException;
use AppBundle\Form\NewsletterSubscriptionType;
use AppBundle\Sitemap\SitemapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction(Request $request, GeoCoder $geoCoder): Response
    {
        if (($user = $this->getUser()) instanceof Adherent) {
            $newsletterSubscription = new NewsletterSubscription(
                $user->getEmailAddress(),
                $user->getPostalCode(),
                $user->getCountry()
            );
        } else {
            $newsletterSubscription = new NewsletterSubscription(null, null, $geoCoder->getCountryCodeFromIp($request->getClientIp()));
        }

        return $this->render('home/index.html.twig', [
            'blocks' => $this->getDoctrine()->getRepository(HomeBlock::class)->findHomeBlocks(),
            'live_links' => $this->getDoctrine()->getRepository(LiveLink::class)->findHomeLiveLinks(),
            'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class, $newsletterSubscription)->createView(),
        ]);
    }

    /**
     * @Route("/sitemap.xml", name="app_sitemap_index", methods={"GET"})
     */
    public function sitemapIndexAction(): Response
    {
        return $this->createXmlResponse($this->get(SitemapFactory::class)->createSitemapIndex());
    }

    /**
     * @Route(
     *     "/sitemap_{type}_{page}.xml",
     *     requirements={"type": AppBundle\Sitemap\SitemapFactory::ALL_TYPES, "page": "\d+"},
     *     defaults={"page": "1"},
     *     name="app_sitemap",
     *     methods={"GET"}
     * )
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
