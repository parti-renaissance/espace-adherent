<?php

namespace AppBundle\Controller;

use AppBundle\Cloudflare\Cloudflare;
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

        return Cloudflare::cacheIndefinitely(
            $this->render('home/index.html.twig', [
                'articles' => $repository->getHomeArticles(),
                'live_links' => $repository->getHomeLiveLinks(),
            ]),
            ['home']
        );
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

        return Cloudflare::cacheIndefinitely(
            $this->render('home/article.html.twig', ['article' => $article]),
            ['articles', 'article-'.$slug]
        );
    }

    /**
     * @Route("/health", name="health")
     * @Method("GET")
     */
    public function healthAction()
    {
        return Cloudflare::cacheIndefinitely(new Response('Healthy'), ['health']);
    }
}
