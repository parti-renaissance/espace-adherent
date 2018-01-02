<?php

namespace AppBundle\Sitemap;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\Media;
use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\Page;
use AppBundle\Repository\MediaRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tackk\Cartographer\ChangeFrequency;
use Tackk\Cartographer\Sitemap;
use Tackk\Cartographer\SitemapIndex;

class SitemapFactory
{
    public const ALL_TYPES = 'citizen_initiatives|committees|content|events|images|main|videos';
    private const PER_PAGE = 10000;
    private const EXPIRATION_TIME = 3600;
    private const TYPE_CITIZEN_INITIATIVES = 'citizen_initiatives';
    private const TYPE_COMMITTEES = 'committees';
    private const TYPE_CONTENT = 'content';
    private const TYPE_EVENTS = 'events';
    private const TYPE_IMAGES = 'images';
    private const TYPE_MAIN = 'main';
    private const TYPE_VIDEOS = 'videos';

    private $manager;
    private $router;
    private $cache;

    public function __construct(ObjectManager $manager, RouterInterface $router, CacheItemPoolInterface $cache)
    {
        $this->manager = $manager;
        $this->router = $router;
        $this->cache = $cache;
    }

    public function createSitemapIndex(): string
    {
        $index = $this->cache->getItem('sitemap_index');

        if (!$index->isHit()) {
            $sitemapIndex = new SitemapIndex();
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_MAIN, 'page' => 1]), null);
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_CONTENT, 'page' => 1]), null);
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_IMAGES, 'page' => 1]), null);
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_VIDEOS, 'page' => 1]), null);

            // Committees
            $totalCount = $this->manager->getRepository(Committee::class)->countSitemapCommittees();
            $pagesCount = ceil($totalCount / self::PER_PAGE);

            for ($i = 1; $i <= $pagesCount; ++$i) {
                $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_COMMITTEES, 'page' => $i]), null);
            }

            // Events
            $totalCount = $this->manager->getRepository(Event::class)->countSitemapEvents();
            $pagesCount = ceil($totalCount / self::PER_PAGE);

            for ($i = 1; $i <= $pagesCount; ++$i) {
                $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_EVENTS, 'page' => $i]), null);
            }

            // Citizen initiatives
            $totalCount = $this->manager->getRepository(CitizenInitiative::class)->countSitemapCitizenInitiatives();
            $pagesCount = ceil($totalCount / self::PER_PAGE);

            for ($i = 1; $i <= $pagesCount; ++$i) {
                $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => self::TYPE_CITIZEN_INITIATIVES, 'page' => $i]), null);
            }

            // AMP
            $sitemapIndex->add($this->generateUrl('amp_sitemap'), null);

            $index->set((string) $sitemapIndex);
            $index->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($index);
        }

        return $index->get();
    }

    public function createAmpSitemap(): string
    {
        $index = $this->cache->getItem('sitemap_amp');

        if (!$index->isHit()) {
            $sitemap = new Sitemap();
            $this->addAmpArticles($sitemap);
            $this->addAmpOrderArticles($sitemap);

            $index->set((string) $sitemap);
            $index->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($index);
        }

        return $index->get();
    }

    public function createSitemap(string $type, int $page): string
    {
        if (self::TYPE_MAIN === $type) {
            return $this->createMainSitemap();
        }

        if (self::TYPE_CONTENT === $type) {
            return $this->createContentSitemap();
        }

        if (self::TYPE_COMMITTEES === $type) {
            return $this->createCommitteesSitemap($page);
        }

        if (self::TYPE_EVENTS === $type) {
            return $this->createEventsSitemap($page);
        }

        if (self::TYPE_CITIZEN_INITIATIVES === $type) {
            return $this->createCitizenInitiativesSitemap($page);
        }

        if (self::TYPE_IMAGES === $type) {
            return $this->createImagesSitemap();
        }

        if (self::TYPE_VIDEOS === $type) {
            return $this->createVideosSitemap();
        }

        return '';
    }

    private function createMainSitemap(): string
    {
        $main = $this->cache->getItem('sitemap_main');

        if (!$main->isHit()) {
            $sitemap = new Sitemap();
            $sitemap->add($this->generateUrl('homepage'), null, ChangeFrequency::HOURLY, 1);
            $sitemap->add($this->generateUrl('donation_index'), null, ChangeFrequency::MONTHLY, 0.8);
            $sitemap->add($this->generateUrl('app_je_marche'), null, ChangeFrequency::NEVER, 0.5);
            $sitemap->add($this->generateUrl('newsletter_subscription'), null, ChangeFrequency::NEVER, 0.5);
            $sitemap->add($this->generateUrl('invitation_form'), null, ChangeFrequency::NEVER, 0.5);

            $main->set((string) $sitemap);
            $main->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($main);
        }

        return $main->get();
    }

    private function createContentSitemap(): string
    {
        $content = $this->cache->getItem('sitemap_content');

        if (!$content->isHit()) {
            $sitemap = new Sitemap();
            $this->addArticlesCategories($sitemap);
            $this->addPages($sitemap);
            $this->addStaticPages($sitemap);
            $this->addArticles($sitemap);
            $sitemap->add($this->generateUrl('app_explainer_index'), null, ChangeFrequency::NEVER, 0.5);
            $this->addOrderArticles($sitemap);

            $content->set((string) $sitemap);
            $content->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($content);
        }

        return $content->get();
    }

    private function createCommitteesSitemap(int $page): string
    {
        $committees = $this->cache->getItem('sitemap_committees_'.$page);

        if (!$committees->isHit()) {
            $sitemap = new Sitemap();
            $this->addCommittees($sitemap, $page, self::PER_PAGE);

            $committees->set((string) $sitemap);
            $committees->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($committees);
        }

        return $committees->get();
    }

    private function createEventsSitemap(int $page): string
    {
        $events = $this->cache->getItem('sitemap_events_'.$page);

        if (!$events->isHit()) {
            $sitemap = new Sitemap();
            $this->addEvents($sitemap, $page, self::PER_PAGE);

            $events->set((string) $sitemap);
            $events->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($events);
        }

        return $events->get();
    }

    private function createCitizenInitiativesSitemap(int $page): string
    {
        $citizenInitiatives = $this->cache->getItem('sitemap_citizen_initiatives_'.$page);

        if (!$citizenInitiatives->isHit()) {
            $sitemap = new Sitemap();
            $this->addCitizenInitiatives($sitemap, $page, self::PER_PAGE);

            $citizenInitiatives->set((string) $sitemap);
            $citizenInitiatives->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($citizenInitiatives);
        }

        return $citizenInitiatives->get();
    }

    private function createImagesSitemap(): string
    {
        $images = $this->cache->getItem('sitemap_images');

        if (!$images->isHit()) {
            $sitemap = new Sitemap();
            $this->addImages($sitemap);

            $images->set((string) $sitemap);
            $images->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($images);
        }

        return $images->get();
    }

    private function createVideosSitemap(): string
    {
        $images = $this->cache->getItem('sitemap_videos');

        if (!$images->isHit()) {
            $sitemap = new Sitemap();
            $this->addVideos($sitemap);

            $images->set((string) $sitemap);
            $images->expiresAfter(self::EXPIRATION_TIME);

            $this->cache->save($images);
        }

        return $images->get();
    }

    private function addArticlesCategories(Sitemap $sitemap)
    {
        $categories = $this->manager->getRepository(ArticleCategory::class)->findAll();

        foreach ($categories as $category) {
            $sitemap->add(
                $this->generateUrl('articles_list', ['category' => $category->getSlug()]),
                null,
                ChangeFrequency::DAILY,
                0.8
            );
        }
    }

    private function addPages(Sitemap $sitemap)
    {
        $sitemap->add($this->generateUrl('program_index'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('map_committees'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_campus'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_mooc'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_campus_internet'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_emmanuel_macron_videos'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_elles_marchent'), null, ChangeFrequency::WEEKLY, 0.6);
    }

    private function addStaticPages(Sitemap $sitemap)
    {
        $pages = $this->manager->getRepository(Page::class)->findAll();

        foreach ($pages as $page) {
            $sitemap->add(
                $this->generateUrl('app_static_page', ['category' => $page->getSlug()]),
                null,
                ChangeFrequency::WEEKLY,
                0.2
            );
        }
    }

    private function addArticles(Sitemap $sitemap)
    {
        $articles = $this->manager->getRepository(Article::class)->findAllPublished();

        foreach ($articles as $article) {
            $sitemap->add(
                $this->generateUrl('article_view', [
                    'articleSlug' => $article->getSlug(),
                    'categorySlug' => $article->getCategory()->getSlug(),
                ]),
                $article->getUpdatedAt()->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addAmpArticles(Sitemap $sitemap)
    {
        $articles = $this->manager->getRepository(Article::class)->findAllPublished();

        foreach ($articles as $article) {
            $sitemap->add(
                $this->generateUrl('amp_article_view', [
                    'articleSlug' => $article->getSlug(),
                    'categorySlug' => $article->getCategory()->getSlug(),
                ]),
                $article->getUpdatedAt()->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addAmpOrderArticles(Sitemap $sitemap)
    {
        $articles = $this->manager->getRepository(OrderArticle::class)->findAllPublished();

        foreach ($articles as $article) {
            $sitemap->add(
                $this->generateUrl('amp_explainer_article_show', ['slug' => $article->getSlug()]),
                $article->getUpdatedAt()->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addOrderArticles(Sitemap $sitemap)
    {
        $articles = $this->manager->getRepository(OrderArticle::class)->findAllPublished();

        foreach ($articles as $article) {
            $sitemap->add(
                $this->generateUrl('app_explainer_article_show', ['slug' => $article->getSlug()]),
                $article->getUpdatedAt()->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addCommittees(Sitemap $sitemap, int $page, int $perPage)
    {
        $committees = $this->manager->getRepository(Committee::class)->findSitemapCommittees($page, $perPage);

        foreach ($committees as $committee) {
            $sitemap->add(
                $this->generateUrl('app_committee_show', $committee),
                null,
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addEvents(Sitemap $sitemap, int $page, int $perPage)
    {
        $events = $this->manager->getRepository(Event::class)->findSitemapEvents($page, $perPage);

        foreach ($events as $event) {
            $sitemap->add(
                $this->generateUrl('app_event_show', [
                    'slug' => $event['slug'],
                ]),
                $event['updatedAt']->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addCitizenInitiatives(Sitemap $sitemap, int $page, int $perPage)
    {
        $citizenInitiatives = $this->manager->getRepository(CitizenInitiative::class)->findSitemapCitizenInitiatives($page, $perPage);

        foreach ($citizenInitiatives as $event) {
            $sitemap->add(
                $this->generateUrl('app_citizen_initiative_show', [
                    'uuid' => $event['uuid'],
                    'slug' => $event['slug'],
                ]),
                $event['updatedAt']->format(\DATE_ATOM),
                ChangeFrequency::WEEKLY,
                0.6
            );
        }
    }

    private function addImages(Sitemap $sitemap)
    {
        $images = $this->manager->getRepository(Media::class)->findSitemapMedias(MediaRepository::TYPE_IMAGE);

        foreach ($images as $image) {
            $imageURL = $this->generateUrl('asset_url', ['path' => $image->getPathWithDirectory()]);
            $sitemap->add($imageURL, null, ChangeFrequency::WEEKLY, 0.6);
        }
    }

    private function addVideos(Sitemap $sitemap)
    {
        $videos = $this->manager->getRepository(Media::class)->findSitemapMedias(MediaRepository::TYPE_VIDEO);

        foreach ($videos as $video) {
            $videoURL = $this->generateUrl('asset_url', ['path' => $video->getPathWithDirectory()]);
            $sitemap->add($videoURL, null, ChangeFrequency::WEEKLY, 0.6);
        }
    }

    private function generateUrl(string $name, array $parameters = [])
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
