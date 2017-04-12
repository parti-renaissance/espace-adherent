<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Page;
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
     * @Route("/algolia/{type}/{slug}", requirements={"type"="proposal|page|article"})
     * @Method("GET")
     */
    public function algoliaAction(Request $request, string $type, string $slug)
    {
        $manager = $this->getDoctrine()->getManager();
        $media = null;

        if ('proposal' === $type) {
            if (!$proposal = $manager->getRepository(Proposal::class)->findOneBySlug($slug)) {
                throw $this->createNotFoundException();
            }

            $media = $proposal->getMedia();
        } elseif ('article' === $type) {
            if (!$article = $manager->getRepository(Article::class)->findOneBySlug($slug)) {
                throw $this->createNotFoundException();
            }

            $media = $article->getMedia();
        }

        $path = $media ? 'images/'.$media->getPath() : 'static/algolia-default-image.jpg';

        $glide = $this->get('app.glide');
        $glide->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            return $glide->getImageResponse($path, [
                'w' => 250,
                'h' => 170,
                'fit' => 'crop',
                'fm' => 'pjpg',
            ]);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException();
        }
    }
}
