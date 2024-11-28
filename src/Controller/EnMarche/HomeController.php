<?php

namespace App\Controller\EnMarche;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
