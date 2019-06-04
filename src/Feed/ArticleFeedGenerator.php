<?php

namespace AppBundle\Feed;

use AppBundle\Entity\Article;
use AppBundle\Feed\Exception\FeedGeneratorException;
use League\CommonMark\CommonMarkConverter;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\FeedInterface;
use Suin\RSSWriter\Item;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleFeedGenerator extends AbstractFeedGenerator
{
    /**
     * @var CommonMarkConverter
     */
    private $markdownParser;

    public function __construct(
        string $locale,
        int $ttl,
        UrlGeneratorInterface $urlGenerator,
        CommonMarkConverter $commonMarkConverter
    ) {
        parent::__construct($locale, $ttl, $urlGenerator);

        $this->markdownParser = $commonMarkConverter;
    }

    public function buildFeed($data): FeedInterface
    {
        if (!\is_array($data) && !$data instanceof \Traversable) {
            throw new FeedGeneratorException('Data must be an instance of \Traversable');
        }

        $feed = new Feed();

        if (!\count($data)) {
            return $feed;
        }

        $mostRecentArticleDateTimestamp = $data[0]->getPublishedAt()->format('U');
        $channel = $this->appendChannel($feed, $mostRecentArticleDateTimestamp, $mostRecentArticleDateTimestamp);

        /** @var Article $article */
        foreach ($data as $article) {
            $this->appendArticleItem($article, $channel);
        }

        return $feed;
    }

    private function appendChannel(FeedInterface $feed, string $pubDate, string $lastBuildDate): Channel
    {
        return (new Channel())
            ->title('En Marche!')
            ->url($this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL))
            ->language($this->locale)
            ->copyright(sprintf('Copyright %d, En Marche!', date('Y')))
            ->pubDate($pubDate)
            ->lastBuildDate($lastBuildDate)
            ->ttl($this->ttl)
            ->appendTo($feed)
        ;
    }

    private function appendArticleItem(Article $article, Channel $channel): Item
    {
        $articleUrl = $this->urlGenerator->generate(
            'article_view',
            [
                'categorySlug' => $article->getCategory()->getSlug(),
                'articleSlug' => $article->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return (new Item())
            ->title($article->getTitle())
            ->url($articleUrl)
            ->description($this->markdownParser->convertToHtml($article->getContent()))
            ->category($article->getCategory()->getName())
            ->pubDate($article->getPublishedAt()->format('U'))
            ->guid($articleUrl, true)
            ->preferCdata(true)
            ->appendTo($channel)
        ;
    }
}
