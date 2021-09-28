<?php

namespace App\OpenGraph;

use App\Utils\EmojisRemover;
use Fusonic\OpenGraph\Consumer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class OpenGraphFetcher
{
    private const HTTP_HEADERS = [
        'User-Agent' => 'facebookexternalhit/1.1',
        'Accept-Language' => 'fr-FR,fr;q=0.8',
    ];

    private $consumer;

    public function __construct()
    {
        $this->consumer = new Consumer(
            new Psr18Client(new NativeHttpClient(['headers' => self::HTTP_HEADERS])),
            new Psr17Factory()
        );
    }

    public function fetch(string $url): ?array
    {
        try {
            $openGraph = $this->consumer->loadUrl($url);
        } catch (\Exception $e) {
            return null;
        }

        $image = reset($openGraph->images);

        return [
            'type' => $openGraph->type,
            'title' => EmojisRemover::remove(str_replace(' on Twitter', '', $openGraph->title)),
            'description' => EmojisRemover::remove(trim($openGraph->description, '“')),
            'site_name' => EmojisRemover::remove($openGraph->siteName),
            'url' => $openGraph->url,
            'image' => $image ? $image->url : null,
        ];
    }
}
