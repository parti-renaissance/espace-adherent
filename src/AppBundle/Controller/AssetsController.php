<?php

namespace AppBundle\Controller;

use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
}
