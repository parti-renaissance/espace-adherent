<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends Controller
{
    /**
     * @Route("/health", name="health", methods={"GET"})
     */
    public function healthAction()
    {
        return new Response('Healthy');
    }
}
