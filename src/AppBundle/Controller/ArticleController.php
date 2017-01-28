<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    /**
     * @Route("/actualites", name="page_actualites")
     * @Method("GET")
     */
    public function actualitesAction()
    {
        return $this->render('article/actualites.html.twig');
    }

    /**
     * @Route("/actualites/videos", name="page_actualites_videos")
     * @Method("GET")
     */
    public function actualitesVideosAction()
    {
        return $this->render('article/videos.html.twig');
    }

    /**
     * @Route("/actualites/discours", name="page_actualites_discours")
     * @Method("GET")
     */
    public function actualitesDiscoursAction()
    {
        return $this->render('article/discours.html.twig');
    }

    /**
     * @Route("/actualites/medias", name="page_actualites_medias")
     * @Method("GET")
     */
    public function actualitesMediasAction()
    {
        return $this->render('article/medias.html.twig');
    }

    /**
     * @Route("/actualites/communiques", name="page_actualites_communiques")
     * @Method("GET")
     */
    public function actualitesCommuniquesAction()
    {
        return $this->render('article/communiques.html.twig');
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
}
