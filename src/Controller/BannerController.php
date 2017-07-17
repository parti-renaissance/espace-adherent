<?php

namespace AppBundle\Controller;

use AppBundle\Entity\HomeBlock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends Controller
{
    /**
     * @Route("/header-banner")
     * @Method("GET")
     */
    public function showHeaderBannerAction(): Response
    {
        return $this->render('banner/call.html.twig', [
            'banner' => $this->getDoctrine()->getRepository(HomeBlock::class)->findOneBy(['position' => 11]),
        ]);
    }
}
