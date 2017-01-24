<?php

namespace AppBundle\Controller;

use AppBundle\Form\NewsletterSubscriptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
