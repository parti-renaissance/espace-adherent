<?php

namespace App\Controller\EnMarche;

use App\Repository\HomeBlockRepository;
use App\Repository\LiveLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    public function indexAction(HomeBlockRepository $homeBlockRepository, LiveLinkRepository $linkRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'blocks' => $homeBlockRepository->findHomeBlocks(),
            'live_links' => $linkRepository->findHomeLiveLinks(),
        ]);
    }
}
