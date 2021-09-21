<?php

namespace App\OpenGraph;

use App\Utils\EmojisRemover;
use App\Utils\PhpConfigurator;
use Symfony\Component\Panther\Client;

class OpenGraphFetcher
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function fetch(string $url): ?array
    {
        try {
            PhpConfigurator::setTimeLimit(30);

            $this->client->request('GET', $this->buildUrl($url));

            $crawler = $this->client->waitFor('//meta[@property="og:description"]', 10);

            $metaTags = $crawler->filterXPath("//*/meta[starts-with(@property, 'og:')]");

            $openGraph = [];
            foreach ($metaTags as $metaTag) {
                $property = substr($metaTag->getAttribute('property'), 3);
                $content = EmojisRemover::remove($metaTag->getAttribute('content'));

                $openGraph[$property] = $content;
            }

            return $openGraph;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function buildUrl(string $url): string
    {
        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $parameters);
        } else {
            $parameters = [];
        }

        $parameters['lang'] = 'fr';

        $parts['query'] = http_build_query($parameters);

        return sprintf(
            '%s://%s%s?%s',
            $parts['scheme'],
            $parts['host'],
            $parts['path'],
            $parts['query']
        );
    }
}
