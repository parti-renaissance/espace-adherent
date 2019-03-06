<?php

namespace Test\AppBundle\Sitemap;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use AppBundle\Entity\Committee;
use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\Page;
use AppBundle\Exception\SitemapException;
use AppBundle\Repository\ArticleCategoryRepository;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\OrderArticleRepository;
use AppBundle\Repository\PageRepository;
use AppBundle\Sitemap\SitemapFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Tackk\Cartographer\Sitemap;

class SitemapFactoryTest extends TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    public function testAddArticle(): void
    {
        $this->initMock();
        $sitemap = new Sitemap();
        $this->invokeReflectionMethod('addArticles', $sitemap);

        $this->assertEquals(1, $sitemap->getUrlCount());
    }

    public function testCreateContentSitemapWithoutHit(): void
    {
        $this->initMock();
        $cacheItemInterface = $this->createMock(CacheItemInterface::class);
        $cacheItemInterface
            ->expects($this->any())
            ->method('isHit')
            ->willReturn(true)
        ;

        $cacheItemInterface
            ->expects($this->any())
            ->method('get')
            ->willReturn((string) new Sitemap())
        ;

        $this->cache
            ->expects($this->any())
            ->method('getItem')
            ->willReturn($cacheItemInterface)
        ;
        $this->cache
            ->expects($this->never())
            ->method('save')
        ;

        $this->invokeReflectionMethod('createContentSitemap');
    }

    public function testCreateContentSitemapWithHit(): void
    {
        $this->initMock();

        $cacheItemInterface = $this->createMock(CacheItemInterface::class);
        $cacheItemInterface
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false)
        ;
        $cacheItemInterface
            ->expects($this->once())
            ->method('set')
        ;
        $cacheItemInterface
            ->expects($this->any())
            ->method('get')
            ->willReturn((string) new Sitemap())
        ;

        $this->cache
            ->method('getItem')
            ->willReturn($cacheItemInterface)
        ;
        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with($cacheItemInterface)
        ;

        $this->invokeReflectionMethod('createContentSitemap');
    }

    public function testSitemapFactoryException(): void
    {
        $committeeRepository = $this->createConfiguredMock(CommitteeRepository::class, ['findSitemapCommittees' => []]);
        $this->objectManager
            ->method('getRepository')
            ->will($this->returnValueMap([
                [Committee::class, $committeeRepository],
            ]))
        ;

        $this->expectException(SitemapException::class);
        $this->invokeReflectionMethod('addCommittees', new Sitemap(), 1, 10);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
    }

    protected function initMock(): void
    {
        $category = new ArticleCategory();
        $category->setSlug('category');

        $article = new Article();
        $article->setSlug('article');
        $article->setCategory($category);
        $article->setUpdatedAt(new \DateTime());

        $orderArticle = new OrderArticle();
        $orderArticle->setSlug('article');
        $orderArticle->setUpdatedAt(new \DateTime());

        $page = new Page();
        $page->setSlug('article');
        $page->setUpdatedAt(new \DateTime());

        $categoriesRepository = $this->createMock(ArticleCategoryRepository::class);
        $categoriesRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn([$category])
        ;

        $articleRepository = $this->createMock(ArticleRepository::class);
        $articleRepository
            ->expects($this->any())
            ->method('findAllPublished')
            ->willReturn([$article])
        ;

        $orderArticleRepository = $this->createMock(OrderArticleRepository::class);
        $orderArticleRepository
            ->expects($this->any())
            ->method('findAllPublished')
            ->willReturn([$orderArticle])
        ;

        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn([$page])
        ;

        $this->objectManager
            ->method('getRepository')
            ->will($this->returnValueMap([
                [ArticleCategory::class, $categoriesRepository],
                [Article::class, $articleRepository],
                [OrderArticle::class, $orderArticleRepository],
                [Page::class, $pageRepository],
            ]))
        ;
    }

    protected function tearDown()
    {
        $this->objectManager = null;
        $this->router = null;
        $this->cache = null;

        parent::tearDown();
    }

    private function invokeReflectionMethod(
        string $methodName,
        Sitemap $sitemap = null,
        int $page = null,
        int $perpage = null
    ) {
        $reflection = new \ReflectionMethod(SitemapFactory::class, $methodName);
        $reflection->setAccessible(true);

        return $reflection->invoke(new SitemapFactory($this->objectManager, $this->router, $this->cache), $sitemap, $page, $perpage);
    }
}
