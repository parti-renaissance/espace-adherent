<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MouvementController extends Controller
{
    /**
     * @Route("/mouvement", name="mouvement")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('mouvement/index.html.twig'),
            ['mouvement']
        );
    }
}
