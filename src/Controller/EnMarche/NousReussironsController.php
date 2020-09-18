<?php

namespace App\Controller\EnMarche;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NousReussironsController extends Controller
{
    /**
     * @Route("/", name="app_nous_reussirons", methods={"GET"})
     */
    public function indexAction(): Response
    {
        return $this->forward('App\Controller\EnMarche\PageController:showPageAction', [
            'slug' => 'nous-reussirons',
        ]);
    }
}
