<?php

namespace AppBundle\Sitemap;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tackk\Cartographer\ChangeFrequency;
use Tackk\Cartographer\Sitemap;
use Tackk\Cartographer\SitemapIndex;

class SitemapFactory
{
    const PER_PAGE = 1000;

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
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => 'main', 'page' => 1]), null);
            $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => 'content', 'page' => 1]), null);

            // Committees
            $totalCount = $this->manager->getRepository(Committee::class)->countSitemapCommittees();
            $pagesCount = ceil($totalCount / self::PER_PAGE);

            for ($i = 1; $i <= $pagesCount; ++$i) {
                $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => 'committees', 'page' => $i]), null);
            }

            // Events
            $totalCount = $this->manager->getRepository(Event::class)->countSitemapEvents();
            $pagesCount = ceil($totalCount / self::PER_PAGE);

            for ($i = 1; $i <= $pagesCount; ++$i) {
                $sitemapIndex->add($this->generateUrl('app_sitemap', ['type' => 'events', 'page' => $i]), null);
            }

            $index->set((string) $sitemapIndex);
            $index->expiresAfter(3600);

            $this->cache->save($index);
        }

        return $index->get();
    }

    public function createSitemap(string $type, int $page): string
    {
        if ('main' === $type) {
            return $this->createMainSitemap();
        }

        if ('content' === $type) {
            return $this->createContentSitemap();
        }

        if ('committees' === $type) {
            return $this->createCommitteesSitemap($page);
        }

        if ('events' === $type) {
            return $this->createEventsSitemap($page);
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
            $main->expiresAfter(3600);

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
            $this->addArticles($sitemap);

            $content->set((string) $sitemap);
            $content->expiresAfter(3600);

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
            $committees->expiresAfter(3600);

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
            $events->expiresAfter(3600);

            $this->cache->save($events);
        }

        return $events->get();
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
        $sitemap->add($this->generateUrl('page_emmanuel_macron'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_emmanuel_macron_revolution'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('program_index'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_notre_organisation'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('map_committees'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_les_comites'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_devenez_benevole'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_mentions_legales'), null, ChangeFrequency::WEEKLY, 0.2);
        $sitemap->add($this->generateUrl('page_politique_cookies'), null, ChangeFrequency::WEEKLY, 0.2);
    }

    private function addArticles(Sitemap $sitemap)
    {
        $articles = $this->manager->getRepository(Article::class)->findAllPublished();

        foreach ($articles as $article) {
            $sitemap->add(
                $this->generateUrl('article_view', ['slug' => $article->getSlug()]),
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

    private function generateUrl(string $name, array $parameters = [])
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
