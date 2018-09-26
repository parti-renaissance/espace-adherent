<?php

namespace AppBundle\Controller;

use AppBundle\React\ReactAppRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReactController extends Controller
{
    public function appAction(ReactAppRegistry $registry, Request $request): Response
    {
        $app = $registry->getApp($request->attributes->get('_react_app'));

        if (!$app) {
            throw $this->createNotFoundException('App not found');
        }

        if (!$app->enableInProduction() && !$this->getParameter('enable_canary')) {
            throw $this->createNotFoundException('This app is disabled in production.');
        }

        $manifest = $registry->readManifest($app);

        if (!$manifest) {
            throw $this->createNotFoundException('Manifest not found, does the app exist and has been built?');
        }

        return $this->render('react.html.twig', ['reactApp' => $app, 'manifest' => $manifest]);
    }
}
