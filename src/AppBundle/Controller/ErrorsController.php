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
    public function indexAction()
    {
        return $this->render('errors/404.html.twig', []);
    }

}
