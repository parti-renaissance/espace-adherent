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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetsController extends Controller
{
    /**
     * @Route("/assets/{path}", requirements={"path"=".+"}, name="asset_url")
     * @Method("GET")
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
     * @Route("/maps/{latitude},{longitude}", requirements={
     *     "latitude"="^%pattern_coordinate%$",
     *     "longitude"="^%pattern_coordinate%$"
     * }, name="map_url")
     *
     * @Method("GET")
     */
    public function mapAction(string $latitude, string $longitude)
    {
        $coordinates = new Coordinates($latitude, $longitude);

        if (!$contents = $this->get('app.map.google_maps_static_provider')->get($coordinates)) {
            throw $this->createNotFoundException('Unable to retrieve the requested static map file');
        }

        return new Response($contents, 200, ['content-type' => 'image/png']);
    }

    /**
     * @Route("/algolia/{type}/{slug}", requirements={"type"="proposal|custom|article|clarification"})
     * @Method("GET")
     */
    public function algoliaAction(Request $request, string $type, string $slug)
    {
        $glide = $this->get('app.glide');
        $glide->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            return $glide->getImageResponse($this->getTypePath($type, $slug), [
                'w' => 250,
                'h' => 170,
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
