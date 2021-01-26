<?php

namespace App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\NewsletterSubscription;
use App\Exception\SitemapException;
use App\Form\NewsletterSubscriptionType;
use App\Repository\HomeBlockRepository;
use App\Repository\LiveLinkRepository;
use App\Sitemap\SitemapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction(
        Request $request,
        GeoCoder $geoCoder,
        HomeBlockRepository $homeBlockRepository,
        LiveLinkRepository $linkRepository
    ): Response {
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
            'blocks' => $homeBlockRepository->findHomeBlocks(),
            'live_links' => $linkRepository->findHomeLiveLinks(),
            'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class, $newsletterSubscription)->createView(),
        ]);
    }

    /**
     * @Route("/sitemap.xml", name="app_sitemap_index", methods={"GET"})
     */
    public function sitemapIndexAction(SitemapFactory $factory): Response
    {
        return $this->createXmlResponse($factory->createSitemapIndex());
    }

    /**
     * @Route(
     *     "/sitemap_{type}_{page}.xml",
     *     requirements={"type": App\Sitemap\SitemapFactory::ALL_TYPES, "page": "\d+"},
     *     defaults={"page": "1"},
     *     name="app_sitemap",
     *     methods={"GET"}
     * )
     */
    public function sitemapAction(string $type, int $page, SitemapFactory $factory): Response
    {
        try {
            return $this->createXmlResponse($factory->createSitemap($type, $page));
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
