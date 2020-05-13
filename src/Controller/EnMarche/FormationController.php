<?php

namespace App\Controller\EnMarche;

use App\Entity\Formation\Module;
use App\Entity\Page;
use App\Repository\Formation\PathRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-formation", name="app_formation_")
 * @Security("is_granted('ROLE_FORMATION_SPACE')")
 */
class FormationController extends Controller
{
    /**
     * @Route(name="home", methods="GET")
     * @Entity("page", expr="repository.findOneBySlug('espace-formation')")
     */
    public function home(Page $page, PathRepository $pathRepository): Response
    {
        return $this->render('formation/home.html.twig', [
            'page' => $page,
            'paths' => $pathRepository->findAllWithAxesAndModules(),
        ]);
    }

    /**
     * @Route("/faq", name="faq", methods="GET")
     * @Entity("page", expr="repository.findOneBySlug('espace-formation/faq')")
     */
    public function faq(Page $page): Response
    {
        return $this->render('formation/faq.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/module/{slug}", name="module", methods="GET")
     */
    public function module(Module $module): Response
    {
        return $this->render('formation/module.html.twig', [
            'module' => $module,
        ]);
    }
}
