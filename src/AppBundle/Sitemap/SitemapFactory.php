<?php

namespace AppBundle\Sitemap;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tackk\Cartographer\ChangeFrequency;
use Tackk\Cartographer\Sitemap;

class SitemapFactory
{
    private $manager;
    private $router;
    private $cache;

    public function __construct(ObjectManager $manager, RouterInterface $router, CacheItemPoolInterface $cache)
    {
        $this->manager = $manager;
        $this->router = $router;
        $this->cache = $cache;
    }

    public function createMainSitemap(): string
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

    public function createContentSitemap(): string
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
        $sitemap->add($this->generateUrl('page_emmanuel_macron_programme'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_notre_organisation'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_la_carte'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_les_comites'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_les_evenements'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_le_mouvement_devenez_benevole'), null, ChangeFrequency::WEEKLY, 0.6);
        $sitemap->add($this->generateUrl('page_mentions_legales'), null, ChangeFrequency::WEEKLY, 0.5);
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

    private function generateUrl(string $name, array $parameters = [])
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
