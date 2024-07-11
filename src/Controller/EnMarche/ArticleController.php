<?php

namespace App\Controller\EnMarche;

use App\AppCodeEnum;
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

    #[Route(path: '/articles/{category}/{page}', requirements: ['category' => '\w+', 'page' => '\d+'], defaults: ['category' => 'tout', 'page' => 1], name: 'articles_list', methods: ['GET'])]
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

    #[Entity('article', expr: 'repository.findOneBySlugAndCategorySlug(articleSlug, categorySlug)')]
    #[Route(path: '/articles/{categorySlug}/{articleSlug}', name: 'article_view', methods: ['GET'])]
    public function articleAction(
        Article $article,
        ArticleRepository $repository,
        Request $request,
        AuthAppUrlManager $appUrlManager,
        string $articlePreviewAdminKey
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        if (!$article->isPublished() && $articlePreviewAdminKey !== $request->query->get('preview')) {
            throw $this->createNotFoundException();
        }

        if ($article->isForRenaissance() xor $isRenaissanceApp) {
            throw $this->createNotFoundException();
        }

        return $this->render($isRenaissanceApp ? 'article/renaissance_article.html.twig' : 'article/article.html.twig', [
            'article' => $article,
            'latestArticles' => $repository->findThreeLatestOtherThan($article),
        ]);
    }

    #[Route(path: '/feed.xml', name: 'articles_feed', methods: ['GET'])]
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
