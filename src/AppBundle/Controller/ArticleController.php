<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    const PER_PAGE = 12;

    /**
     * @Route("/feed", name="articles_feed")
     * @Method("GET")
     */
    public function feedAction()
    {
        return new Response(
            $this->get('app.feed_generator.article')->buildFeed(
                $this->getDoctrine()->getRepository(Article::class)->findAllForFeed()
            )->render(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/rss+xml; charset=UTF-8',
            ]
        );
    }

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
        $categoriesRepo = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $category = $categoriesRepo->findOneBySlug($category);

        if (!$category) {
            throw $this->createNotFoundException();
        }

        $page = (int) $page;

        $categories = $categoriesRepo->findAll();
        $articlesRepo = $this->getDoctrine()->getRepository(Article::class);
        $articlesCount = $articlesRepo->countAllByCategory($category);

        if (!$this->isPaginationValid($articlesCount, $page)) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/actualites.html.twig', [
            'current' => $category,
            'categories' => $categories,
            'articles' => $articlesRepo->findByCategoryPaginated($category, $page, self::PER_PAGE),
            'currentPage' => $page,
            'totalPages' => ceil($articlesCount / self::PER_PAGE),
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

    /**
     * @param int $articlesCount
     * @param int $requestedPageNumber
     * @param int $itemsPerPage
     *
     * @return bool
     */
    private function isPaginationValid(int $articlesCount, int $requestedPageNumber, int $itemsPerPage = self::PER_PAGE): bool
    {
        if (!$articlesCount) {
            return false;
        }

        return $requestedPageNumber <= (int) ceil($articlesCount / $itemsPerPage);
    }
}
