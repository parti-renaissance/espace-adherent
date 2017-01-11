<?php

namespace AppBundle\Controller;

use AppBundle\Form\NewsletterSubscriptionType;
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
        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('home/index.html.twig', [
                'articles' => [],
                'live_links' => [],
                'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class)->createView(),
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

        return $this->get('app.cloudflare')->cacheIndefinitely(
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
        return $this->get('app.cloudflare')->cacheIndefinitely(new Response('Healthy'), ['health']);
    }
}
