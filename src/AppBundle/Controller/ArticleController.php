<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Psr\Cache\CacheItemPoolInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    const PER_PAGE = 12;

    /**
     * @Route(
     *     "/articles/{category}/{page}",
     *     requirements={"page"="\d+"},
     *     defaults={"page"=1},
     *     name="articles_list"
     * )
     * @Method("GET")
     */
    public function actualitesAction($category, $page): Response
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
    public function articleAction($slug): Response
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
     * @Route("/feed.xml", name="articles_feed")
     * @Method("GET")
     */
    public function feedAction(): Response
    {
        /** @var CacheItemPoolInterface $cache */
        $cache = $this->get('cache.app');
        $cachedRenderedFeed = $cache->getItem('rss_feed');

        if (!$cachedRenderedFeed->isHit()) {
            $generator = $this->get('app.feed_generator.article');
            $feed = $generator->buildFeed($this->getDoctrine()->getRepository(Article::class)->findAllForFeed());

            $cachedRenderedFeed->set($feed->render());
            $cachedRenderedFeed->expiresAfter($this->getParameter('feed_ttl') * 60);

            $cache->save($cachedRenderedFeed);
        }

        return new Response($cachedRenderedFeed->get(), Response::HTTP_OK, ['Content-Type' => 'application/rss+xml']);
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
