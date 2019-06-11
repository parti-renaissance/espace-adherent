<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Formation\Article;
use AppBundle\Entity\Page;
use AppBundle\Repository\Formation\AxeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-formation", name="app_formation_")
 * @Security("is_granted('ROLE_HOST')")
 */
class FormationController extends Controller
{
    /**
     * @Route(name="home", methods="GET")
     * @Entity("page", expr="repository.findOneBySlug('espace-formation')")
     */
    public function home(Page $page, AxeRepository $axeRepository): Response
    {
        return $this->render('formation/home.html.twig', [
            'page' => $page,
            'axes' => $axeRepository->findAllWithArticles(),
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
     * @Route("/article/{slug}", name="article", methods="GET")
     */
    public function article(Article $article): Response
    {
        return $this->render('formation/article.html.twig', [
            'article' => $article,
        ]);
    }
}
