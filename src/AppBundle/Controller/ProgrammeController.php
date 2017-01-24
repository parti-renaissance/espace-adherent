<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProgrammeController extends Controller
{
    /**
     * @Route("/programme", name="programme")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('programme/index.html.twig'),
            ['programme']
        );
    }
}
