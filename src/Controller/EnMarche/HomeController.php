<?php

namespace App\Controller\EnMarche;

use App\Exception\SitemapException;
use App\Repository\HomeBlockRepository;
use App\Repository\LiveLinkRepository;
use App\Sitemap\SitemapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction(HomeBlockRepository $homeBlockRepository, LiveLinkRepository $linkRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'blocks' => $homeBlockRepository->findHomeBlocks(),
            'live_links' => $linkRepository->findHomeLiveLinks(),
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
