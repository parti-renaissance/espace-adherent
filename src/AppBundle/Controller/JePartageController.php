<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class JePartageController extends Controller
{
    /**
     * @Route("/jepartage", name="app_je_partage")
     * @Method("GET")
     */
    public function allAction(): Response
    {
        return $this->render('jepartage/index.html.twig');
    }
}
