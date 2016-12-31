<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction()
    {
        $repository = $this->get('app.filesystem.article_repository');

        return $this->render('home/index.html.twig', [
            'articles' => $repository->getHomeArticles(),
            'live_links' => $repository->getHomeLiveLinks(),
        ]);
    }

    /**
     * @Route("/article/{slug}", name="article_view")
     * @Method("GET")
     */
    public function articleAction($slug)
    {
        $article = $this->get('app.filesystem.article_repository')->getArticle($slug);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        return $this->render('home/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/health", name="health")
     * @Method("GET")
     */
    public function healthAction()
    {
        return new Response('Healthy');
    }
}
