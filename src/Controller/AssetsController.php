<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Clarification;
use App\Entity\CustomSearchResult;
use App\Entity\Proposal;
use App\Geocoder\Coordinates;
use App\Map\StaticMapProviderInterface;
use App\Timeline\TimelineImageFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetsController extends AbstractController
{
    private const WIDTH = 250;
    private const HEIGHT = 170;

    private const EXTENSIONS_TYPES = [
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/assets/{path}", requirements={"path": ".+"}, name="asset_url", methods={"GET"})
     * @Cache(maxage=900, smaxage=900)
     */
    public function assetAction(Server $glide, FilesystemInterface $storage, string $path, Request $request): Response
    {
        $parameters = $request->query->all();

        if (!empty($parameters['mime_type'])) {
            return new Response($storage->read($path), Response::HTTP_OK, [
                'Content-Type' => $parameters['mime_type'],
            ]);
        }

        if (\count($parameters) > 0) {
            try {
                // No signature validation if no parameters
                // added to generate URL without parameters that not produce 404, useful especially for sitemap
                SignatureFactory::create($this->getParameter('kernel.secret'))->validateRequest($path, $parameters);
            } catch (SignatureException $e) {
                throw $this->createNotFoundException('', $e);
            }
        }

        if (\array_key_exists($extension = substr($path, -3), self::EXTENSIONS_TYPES)) {
            return new Response($storage->read($path), Response::HTTP_OK, [
                'Content-Type' => self::EXTENSIONS_TYPES[$extension],
            ]);
        }

        $glide->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            $response = $glide->getImageResponse($path, $request->query->all());
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('', $e);
        }

        return $response;
    }

    /**
     * @Route(
     *     "/maps/{latitude},{longitude}",
     *     requirements={"latitude": "^%pattern_coordinate%$", "longitude": "^%pattern_coordinate%$"},
     *     name="map_url",
     *     methods={"GET"}
     * )
     * @Cache(maxage=900, smaxage=900)
     */
    public function mapAction(
        Request $request,
        StaticMapProviderInterface $mapProvider,
        string $latitude,
        string $longitude
    ): Response {
        if ($request->query->has('algolia')) {
            $size = self::WIDTH.'x'.self::HEIGHT;
        }

        if ($contents = $mapProvider->get(new Coordinates($latitude, $longitude), $size ?? null)) {
            return new Response($contents, Response::HTTP_OK, ['content-type' => 'image/png']);
        }

        return new BinaryFileResponse($this->createWhiteImage(), Response::HTTP_OK, ['content-type' => 'image/png']);
    }

    /**
     * @Route("/video/homepage.{format}", requirements={"format": "mov|mp4"}, name="homepage_video_url", methods={"GET"})
     * @Cache(maxage=60, smaxage=60)
     */
    public function videoAction(FilesystemInterface $storage, string $format): Response
    {
        return new Response(
            $storage->read('static/videos/homepage.'.$format),
            Response::HTTP_OK,
            ['Content-Type' => 'video/'.$format]
        );
    }

    /**
     * @Route("/algolia/{type}/{slug}", requirements={"type": "proposal|custom|article|clarification"}, methods={"GET"})
     * @Cache(maxage=900, smaxage=900)
     */
    public function algoliaAction(Server $glide, Request $request, string $type, string $slug): Response
    {
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
            $entity = $this->manager->getRepository(CustomSearchResult::class)->find((int) $slug);
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

    private function getTypeRepository(string $type): ServiceEntityRepositoryInterface
    {
        if ('proposal' === $type) {
            return $this->manager->getRepository(Proposal::class);
        }

        if ('clarification' === $type) {
            return $this->manager->getRepository(Clarification::class);
        }

        if ('article' === $type) {
            return $this->manager->getRepository(Article::class);
        }

        return $this->manager->getRepository(Article::class);
    }

    /**
     * Creates a transparent image PNG with sizes 1px x 1px.
     *
     * @return string Image path
     */
    private function createWhiteImage(): string
    {
        $imagePath = $this->getParameter('kernel.cache_dir').\DIRECTORY_SEPARATOR.'white_image.png';

        $image = imagecreatetruecolor(1, 1);
        imagesavealpha($image, true);
        $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $color);
        imagepng($image, $imagePath);

        return $imagePath;
    }

    /**
     * @Route("/image-transformer.jpg", name="asset_timeline", methods={"GET"})
     * @Cache(maxage=900, smaxage=900)
     */
    public function timelineImageAction(Request $request, TimelineImageFactory $imageFactory): Response
    {
        $locale = 'fr';

        if (preg_match('#/en/#', $request->headers->get('referer'))) {
            $locale = 'en';
        }

        return new BinaryFileResponse($imageFactory->createImage($locale), Response::HTTP_OK, [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
