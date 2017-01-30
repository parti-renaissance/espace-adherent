<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorsController extends Controller
{
    /**
     * @Route("/404", name="404")
     * @Method("GET")
     */
    public function quatreCentQuatreAction()
    {
        return $this->render('errors/404.html.twig', []);
    }

    /**
     * @Route("/500", name="500")
     * @Method("GET")
     */
    public function cinqCentAction()
    {
        return $this->render('errors/500.html.twig', []);
    }

    /**
     * @Route("/503", name="503")
     * @Method("GET")
     */
    public function cinqCentTroisAction()
    {
        return $this->render('errors/503.html.twig', []);
    }
}
