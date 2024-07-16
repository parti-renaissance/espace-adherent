<?php

namespace App\Controller\EnMarche;

use App\Repository\LiveLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    public function indexAction(LiveLinkRepository $linkRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'live_links' => $linkRepository->findHomeLiveLinks(),
        ]);
    }
}
