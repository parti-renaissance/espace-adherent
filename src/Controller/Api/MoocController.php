<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Mooc\Mooc;
use AppBundle\Normalizer\MoocNormalizer;
use AppBundle\Sitemap\SitemapFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/mooc")
 */
class MoocController extends Controller
{
    /**
     * @Route("/sitemap.xml", name="api_mooc_sitemap")
     * @Method("GET")
     */
    public function sitemapAction(SitemapFactory $sitemapFactory): Response
    {
        return $this->createXmlResponse(
            $sitemapFactory->createMoocSitemap($this->getParameter('mooc_base_url'))
        );
    }

    /**
     * @Route("/{slug}", name="api_mooc")
     * @Method("GET")
     * @Entity("mooc", expr="repository.findOneBySlug(slug)")
     */
    public function moocAction(Mooc $mooc, MoocNormalizer $normalizer): Response
    {
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return new JsonResponse(
            $serializer->serialize($mooc, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    private function createXmlResponse(string $content): Response
    {
        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/xml',
        ]);
    }
}
