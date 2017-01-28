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
                'blocks' => $this->getDoctrine()->getRepository('AppBundle:HomeBlock')->findHomeBlocks(),
                'live_links' => $this->getDoctrine()->getRepository('AppBundle:LiveLink')->findHomeLiveLinks(),
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
        $article = $this->getDoctrine()->getRepository('AppBundle:Article')->findOneBySlug($slug);

        if (!$article || !$article->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('home/article.html.twig', ['article' => $article]),
            ['articles', 'article-'.$article->getId()]
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
