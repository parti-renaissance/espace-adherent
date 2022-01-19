<?php

namespace App\Controller\Api;

use App\Entity\Mooc\Mooc;
use App\Repository\MoocRepository;
use App\Sitemap\SitemapFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mooc")
 */
class MoocController extends AbstractController
{
    /**
     * @Route("", name="api_mooc_landing", methods={"GET"})
     */
    public function moocLandingPageAction(MoocRepository $moocRepository): Response
    {
        return $this->json($moocRepository->findAllOrdered(), Response::HTTP_OK, [], ['groups' => ['mooc_list']]);
    }

    /**
     * @Route("/sitemap.xml", name="api_mooc_sitemap", methods={"GET"})
     */
    public function sitemapAction(SitemapFactory $sitemapFactory): Response
    {
        return $this->createXmlResponse(
            $sitemapFactory->createMoocSitemap($this->getParameter('mooc_base_url'))
        );
    }

    /**
     * @Route("/{slug}", name="api_mooc", methods={"GET"})
     * @Entity("mooc", expr="repository.findOneBySlug(slug)")
     */
    public function moocAction(Mooc $mooc): Response
    {
        return $this->json($mooc, Response::HTTP_OK, [], ['groups' => ['mooc_read']]);
    }

    private function createXmlResponse(string $content): Response
    {
        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }
}
