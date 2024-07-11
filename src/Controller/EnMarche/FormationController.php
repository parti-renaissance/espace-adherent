<?php

namespace App\Controller\EnMarche;

use App\Entity\Formation\Module;
use App\Entity\Page;
use App\Repository\Formation\PathRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_FORMATION_SPACE')]
#[Route(path: '/espace-formation', name: 'app_formation_')]
class FormationController extends AbstractController
{
    #[Entity('page', expr: "repository.findOneBySlug('espace-formation')")]
    #[Route(name: 'home', methods: 'GET')]
    public function home(Page $page, PathRepository $pathRepository): Response
    {
        return $this->render('formation/home.html.twig', [
            'page' => $page,
            'paths' => $pathRepository->findAllWithAxesAndModules(),
        ]);
    }

    #[Entity('page', expr: "repository.findOneBySlug('espace-formation/faq')")]
    #[Route(path: '/faq', name: 'faq', methods: 'GET')]
    public function faq(Page $page): Response
    {
        return $this->render('formation/faq.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(path: '/module/{slug}', name: 'module', methods: 'GET')]
    public function module(Module $module): Response
    {
        return $this->render('formation/module.html.twig', [
            'module' => $module,
        ]);
    }
}
