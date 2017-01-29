<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    const PER_PAGE = 20;

    /**
     * @Route(
     *     "/articles/{category}/{page}",
     *     requirements={"page"="\d+"},
     *     defaults={"page"=1},
     *     name="articles_list"
     * )
     * @Method("GET")
     */
    public function actualitesAction($category, $page)
    {
        $doctrine = $this->getDoctrine();
        $categoriesRepository = $doctrine->getRepository(ArticleCategory::class);

        $category = $categoriesRepository->findOneBySlug($category);

        if (!$category) {
            throw $this->createNotFoundException();
        }

        $page = (int) $page;

        $categories = $doctrine->getRepository(ArticleCategory::class)->findAll();
        $articles = $doctrine->getRepository(Article::class)->findByCategoryPaginated($category, $page, self::PER_PAGE);

        if (empty($articles)) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/actualites.html.twig', [
            'current' => $category,
            'categories' => $categories,
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => ceil(count($articles) / self::PER_PAGE),
        ]);
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

        return $this->render('article/article.html.twig', [
            'article' => $article,
        ]);
    }
}
