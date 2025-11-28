<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/robots.txt', methods: 'GET')]
class RobotsController extends AbstractController
{
    public function __invoke(Request $request, string $app_domain, string $appEnvironment, string $adminRenaissanceHost): Response
    {
        if ('production' !== $appEnvironment || $app_domain === $adminRenaissanceHost) {
            return new Response("User-agent: *\nDisallow: /", 200, ['Content-Type' => 'text/plain']);
        }

        return new Response(
            <<<ROBOTS
                User-agent: *
                Disallow: /*callback=/connexion
                Disallow: /*callback=/adhesion
                Disallow: /documents-partages/*
                Disallow: /cdn-cgi/

                Sitemap: https://parti-renaissance.fr/sitemap.xml
                ROBOTS, 200, ['Content-Type' => 'text/plain']);
    }
}
