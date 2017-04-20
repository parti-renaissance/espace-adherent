<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends Controller
{
    /**
     * @Route("/health", defaults={"_enable_campaign_silence"=true}, name="health")
     * @Method("GET")
     */
    public function healthAction()
    {
        return new Response('Healthy');
    }
}
