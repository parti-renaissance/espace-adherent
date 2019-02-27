<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Formation\Article;
use AppBundle\Entity\Formation\Axe;
use AppBundle\Entity\Page;
use AppBundle\Repository\Formation\AxeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-formation")
 * @Security("is_granted('ROLE_HOST')")
 */
class FormationController extends Controller
{
    /**
     * @Route(name="app_formation_intro", methods="GET")
     * @Entity("page", expr="repository.findOneBySlug('espace-formation-intro')")
     */
    public function intro(Page $page): Response
    {
        return $this->render('formation/intro.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/mon-parcours", name="app_formation_home", methods="GET")
     * @Entity("page", expr="repository.findOneBySlug('espace-formation')")
     */
    public function home(Page $page, AxeRepository $axeRepository): Response
    {
        return $this->render('formation/home.html.twig', [
            'page' => $page,
            'axes' => $axeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/axe/{slug}", name="app_formation_axe", methods="GET")
     */
    public function axe(Axe $axe): Response
    {
        return $this->render('formation/axe.html.twig', [
            'axe' => $axe,
        ]);
    }

    /**
     * @Route("/article/{slug}", name="app_formation_article", methods="GET")
     */
    public function article(Article $article): Response
    {
        return $this->render('formation/article.html.twig', [
            'article' => $article,
        ]);
    }
}
