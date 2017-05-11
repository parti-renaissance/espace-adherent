<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\CustomSearchResult;
use AppBundle\Entity\Proposal;
use AppBundle\Geocoder\Coordinates;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetsController extends Controller
{
    private const WIDTH = 250;
    private const HEIGHT = 170;

    /**
     * @Route("/assets/{path}", defaults={"_enable_campaign_silence"=true}, requirements={"path"=".+"}, name="asset_url")
     * @Method("GET")
     * @Cache(maxage=900, smaxage=900)
     */
    public function assetAction($path, Request $request)
    {
        $parameters = $request->query->all();

        try {
            SignatureFactory::create($this->getParameter('kernel.secret'))->validateRequest($path, $parameters);
        } catch (SignatureException $e) {
            throw $this->createNotFoundException();
        }

        $glide = $this->get('app.glide');
        $glide->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            $response = $glide->getImageResponse($path, $request->query->all());
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException();
        }

        return $response;
    }

    /**
     * @Route(
     *     "/maps/{latitude},{longitude}",
     *     defaults={"_enable_campaign_silence"=true},
     *     requirements={"latitude"="^%pattern_coordinate%$", "longitude"="^%pattern_coordinate%$"},
     *     name="map_url"
     * )
     * @Method("GET")
     * @Cache(maxage=900, smaxage=900)
     */
    public function mapAction(Request $request, string $latitude, string $longitude)
    {
        $coordinates = new Coordinates($latitude, $longitude);
        $size = $request->query->has('algolia') ? self::WIDTH.'x'.self::HEIGHT : null;

        if (!$contents = $this->get('app.map.google_maps_static_provider')->get($coordinates, $size)) {
            throw $this->createNotFoundException('Unable to retrieve the requested static map file');
        }

        return new Response($contents, 200, ['content-type' => 'image/png']);
    }

    /**
     * @Route(
     *     "/video/homepage.{format}",
     *     requirements={"format"="mov|mp4"},
     *     defaults={"_enable_campaign_silence"=true},
     *     name="homepage_video_url"
     * )
     * @Method("GET")
     * @Cache(maxage=60, smaxage=60)
     */
    public function videoAction(string $format)
    {
        return new Response(
            $this->get('app.storage')->read('static/videos/homepage.'.$format),
            Response::HTTP_OK,
            ['Content-Type' => 'video/'.$format]
        );
    }

    /**
     * @Route(
     *     "/algolia/{type}/{slug}",
     *     defaults={"_enable_campaign_silence"=true},
     *     requirements={"type"="proposal|custom|article|clarification"}
     * )
     * @Method("GET")
     * @Cache(maxage=900, smaxage=900)
     */
    public function algoliaAction(Request $request, string $type, string $slug)
    {
        $glide = $this->get('app.glide');
        $glide->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            return $glide->getImageResponse($this->getTypePath($type, $slug), [
                'w' => self::WIDTH,
                'h' => self::HEIGHT,
                'fit' => 'crop',
                'fm' => 'pjpg',
            ]);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException();
        }
    }

    private function getTypePath(string $type, string $slug): string
    {
        if ('custom' === $type) {
            $entity = $this->getDoctrine()->getRepository(CustomSearchResult::class)->find((int) $slug);
        } else {
            $entity = $this->getTypeRepository($type)->findOneBySlug($slug);
        }

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        if (!$entity->getMedia()) {
            return 'static/algolia/default.jpg';
        }

        return 'images/'.$entity->getMedia()->getPath();
    }

    private function getTypeRepository(string $type)
    {
        $manager = $this->getDoctrine()->getManager();

        if ('proposal' === $type) {
            return $manager->getRepository(Proposal::class);
        }

        if ('clarification' === $type) {
            return $manager->getRepository(Clarification::class);
        }

        if ('article' === $type) {
            return $manager->getRepository(Article::class);
        }

        return $manager->getRepository(Article::class);
    }
}
