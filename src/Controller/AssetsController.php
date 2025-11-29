<?php

declare(strict_types=1);

namespace App\Controller;

use League\Flysystem\FilesystemOperator;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class AssetsController extends AbstractController
{
    private const EXTENSIONS_TYPES = [
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    #[Cache(maxage: 900, smaxage: 900)]
    #[Route(path: '/assets/{path}', name: 'asset_url', requirements: ['path' => '.+'], methods: ['GET'])]
    public function assetAction(Server $glide, FilesystemOperator $defaultStorage, string $path, Request $request): Response
    {
        if (!$defaultStorage->has($path)) {
            throw $this->createNotFoundException();
        }

        $parameters = $request->query->all();

        if (!empty($parameters['mime_type'])) {
            return new Response($defaultStorage->read($path), Response::HTTP_OK, [
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

        if (\array_key_exists($extension = pathinfo($path, \PATHINFO_EXTENSION), self::EXTENSIONS_TYPES)) {
            return new Response($defaultStorage->read($path), Response::HTTP_OK, [
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

    #[Cache(maxage: 60, smaxage: 60)]
    #[Route(path: '/video/homepage.{format}', name: 'homepage_video_url', requirements: ['format' => 'mov|mp4'], methods: ['GET'])]
    public function videoAction(FilesystemOperator $defaultStorage, string $format): Response
    {
        return new Response(
            $defaultStorage->read('static/videos/homepage.'.$format),
            Response::HTTP_OK,
            ['Content-Type' => 'video/'.$format]
        );
    }
}
