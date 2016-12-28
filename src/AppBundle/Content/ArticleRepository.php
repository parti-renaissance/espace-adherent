<?php

namespace AppBundle\Content;

use AppBundle\Content\Model\Article;
use AppBundle\Content\Model\HomeArticle;
use AppBundle\Content\Model\HomeLiveLink;
use League\CommonMark\CommonMarkConverter;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class ArticleRepository
{
    const PATH_HOME = 'home.yml';
    const PATH_ARTICLES = 'articles';
    const PATH_METADATA = 'metadata.yml';
    const PATH_CONTENT = 'content.md';

    private $filesystem;
    private $commonMarkConverter;
    private $logger;

    private $homeConfiguration;

    public function __construct(Filesystem $filesystem, CommonMarkConverter $commonMarkConverter, LoggerInterface $logger)
    {
        $this->filesystem = $filesystem;
        $this->commonMarkConverter = $commonMarkConverter;
        $this->logger = $logger;
    }

    /**
     * Parse the YAML home configuration file and return the home articles.
     *
     * Returns an empty array if an error occured during YAML parsing.
     *
     * @return HomeArticle[]
     */
    public function getHomeArticles(): array
    {
        try {
            if (!$this->homeConfiguration) {
                $this->fetchAndParseHomeConfiguration();
            }

            return $this->mapHomeArticles($this->homeConfiguration['articles']);
        } catch (\Exception $exception) {
            $this->logger->critical('Home articles can not be read', [
                'exception' => $exception,
            ]);

            return [];
        }
    }

    /**
     * Parse the YAML home configuration file and return the home live links.
     *
     * Returns an empty array if an error occured during YAML parsing.
     *
     * @return HomeLiveLink[]
     */
    public function getHomeLiveLinks(): array
    {
        try {
            if (!$this->homeConfiguration) {
                $this->fetchAndParseHomeConfiguration();
            }

            return $this->mapLiveLink($this->homeConfiguration['live_links']);
        } catch (\Exception $exception) {
            $this->logger->critical('Live links can not be read', [
                'exception' => $exception,
            ]);

            return [];
        }
    }

    /**
     * Parse the YAML and Mardown for a given article slug.
     *
     * Returns null if the article does not exists.
     *
     * @param string $slug
     *
     * @return Article|false
     */
    public function getArticle($slug)
    {
        try {
            $metadataRaw = $this->filesystem->read(self::PATH_ARTICLES.'/'.$slug.'/'.self::PATH_METADATA);
            $contentRaw = $this->filesystem->read(self::PATH_ARTICLES.'/'.$slug.'/'.self::PATH_CONTENT);
        } catch (\Exception $exception) {
            return;
        }

        return $this->mapArticle(Yaml::parse($metadataRaw), $contentRaw);
    }

    private function fetchAndParseHomeConfiguration()
    {
        $this->homeConfiguration = Yaml::parse($this->filesystem->read(self::PATH_HOME));
    }

    private function mapHomeArticles(array $rawData)
    {
        $items = [];

        foreach ($rawData as $key => $rawItem) {
            $items[$key] = new HomeArticle(
                (string) $rawItem['type'],
                (string) $rawItem['title'],
                isset($rawItem['subtitle']) ? (string) $rawItem['subtitle'] : '',
                (string) $rawItem['image'],
                (string) $rawItem['link']
            );
        }

        return $items;
    }

    private function mapLiveLink(array $rawData)
    {
        $items = [];

        foreach ($rawData as $key => $rawItem) {
            $items[$key] = new HomeLiveLink((string) $rawItem['title'], (string) $rawItem['link']);
        }

        return $items;
    }

    private function mapArticle(array $metadataRaw, $contentRaw)
    {
        return new Article(
            isset($metadataRaw['title']) ? $metadataRaw['title'] : '',
            isset($metadataRaw['description']) ? $metadataRaw['description'] : '',
            isset($metadataRaw['date']) ? $metadataRaw['date'] : '',
            $this->commonMarkConverter->convertToHtml($contentRaw)
        );
    }
}
