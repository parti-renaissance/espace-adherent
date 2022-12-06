<?php

namespace App\Controller\EnMarche;

use App\AppCodeEnum;
use App\Entity\Administrator;
use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Feed\ArticleFeedGenerator;
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\ArticleCategoryRepository;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class ArticleController extends AbstractController
{
    public const PER_PAGE = 12;

    /**
     * @Route(
     *     "/articles/{category}/{page}",
     *     requirements={"category": "\w+", "page": "\d+"},
     *     defaults={"category": "tout", "page": 1},
     *     name="articles_list",
     *     methods={"GET"}
     * )
     */
    public function actualitesAction(
        ArticleCategoryRepository $categoriesRepo,
        ArticleRepository $articlesRepo,
        Request $request,
        string $category,
        int $page
    ): Response {
        if ('/articles/'.ArticleCategory::DEFAULT_CATEGORY === $request->getRequestUri()) {
            return $this->redirectToRoute('articles_list', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        $noFilterByCategory = new ArticleCategory('Toute l\'actualité', ArticleCategory::DEFAULT_CATEGORY);

        $articleCategory = !ArticleCategory::isDefault($category)
            ? $categoriesRepo->findOneBySlug($category)
            : $noFilterByCategory;

        if (!$articleCategory) {
            throw $this->createNotFoundException();
        }

        $categories = $categoriesRepo->findBy(['display' => true]);
        array_unshift($categories, $noFilterByCategory);
        $articlesCount = $articlesRepo->countAllByCategory($category);

        if (!$this->isPaginationValid($articlesCount, $page)) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/actualites.html.twig', [
            'current' => $articleCategory,
            'categories' => $categories,
            'articles' => $articlesRepo->findByCategoryPaginated($category, $page, self::PER_PAGE),
            'currentPage' => $page,
            'totalPages' => ceil($articlesCount / self::PER_PAGE),
        ]);
    }

    /**
     * @Route("/articles/{categorySlug}/{articleSlug}", name="article_view", methods={"GET"})
     * @Entity("article", expr="repository.findOneBySlugAndCategorySlug(articleSlug, categorySlug)")
     */
    public function articleAction(
        Article $article,
        ArticleRepository $repository,
        Request $request,
        AuthAppUrlManager $appUrlManager
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);

        if ($article->isForRenaissance() xor AppCodeEnum::isRenaissanceApp($appCode)) {
            throw $this->createNotFoundException();
        }

        return $this->render(AppCodeEnum::isRenaissanceApp($appCode) ? 'article/renaissance_article.html.twig' : 'article/article.html.twig', [
            'article' => $article,
            'latestArticles' => $repository->findThreeLatestOtherThan($article),
        ]);
    }

    /**
     * @Route("/articles/preview/{categorySlug}/{articleSlug}", name="article_preview")
     * @Entity("article", expr="repository.findOneBySlugAndCategorySlug(articleSlug, categorySlug, false)")
     */
    public function previewAction(
        Article $article,
        ArticleRepository $repository,
        Request $request,
        AuthAppUrlManager $appUrlManager,
        string $renaissanceArticlePreviewKey
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        $user = $this->getUser();

        if (!$isRenaissanceApp && !$user instanceof Administrator) {
            throw $this->createNotFoundException();
        }

        if ($isRenaissanceApp && ($request->query->get('bypass') !== $renaissanceArticlePreviewKey)) {
            throw $this->createNotFoundException();
        }

        if ($article->isForRenaissance() xor $isRenaissanceApp) {
            throw $this->createNotFoundException();
        }

        return $this->render(AppCodeEnum::isRenaissanceApp($appCode) ? 'article/renaissance_article.html.twig' : 'article/article.html.twig', [
            'article' => $article,
            'latestArticles' => $repository->findThreeLatestOtherThan($article),
        ]);
    }

    /**
     * @Route("/feed.xml", name="articles_feed", methods={"GET"})
     */
    public function feedAction(
        ArticleFeedGenerator $generator,
        ArticleRepository $repository,
        CacheInterface $cache
    ): Response {
        $cachedRenderedFeed = $cache->getItem('rss_feed');

        if (!$cachedRenderedFeed->isHit()) {
            $feed = $generator->buildFeed($repository->findAllForFeed());

            $cachedRenderedFeed->set($feed->render());
            $cachedRenderedFeed->expiresAfter($this->getParameter('feed_ttl') * 60);

            $cache->save($cachedRenderedFeed);
        }

        return new Response($cachedRenderedFeed->get(), Response::HTTP_OK, ['Content-Type' => 'application/rss+xml']);
    }

    private function isPaginationValid(
        int $articlesCount,
        int $requestedPageNumber,
        int $itemsPerPage = self::PER_PAGE
    ): bool {
        if (!$articlesCount) {
            return false;
        }

        return $requestedPageNumber <= (int) ceil($articlesCount / $itemsPerPage);
    }
}
