<?php

namespace AppBundle\Controller;

use AppBundle\React\ReactAppRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReactController extends Controller
{
    public function appAction(ReactAppRegistry $registry, Request $request)
    {
        $manifest = $registry->readManifest($request->attributes->get('_react_app'));
        if (!$manifest) {
            throw $this->createNotFoundException('Manifest not found, does the app exist and has been built?');
        }

        return $this->render('react.html.twig', ['manifest' => $manifest]);
    }
}
