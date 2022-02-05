<?php

namespace App\Facebook;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PictureImporter
{
    private HttpClientInterface $client;
    private CacheItemPoolInterface $cache;

    public function __construct(HttpClientInterface $client, CacheItemPoolInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function import(int $facebookId): ?string
    {
        $cacheItem = $this->cache->getItem('fb_picture_'.$facebookId);

        if (!$cacheItem->isHit()) {
            try {
                $content = $this->client->request('GET', '/'.$facebookId.'/picture?width=1500&height=1500')->getContent();
            } catch (TransportExceptionInterface $exception) {
                return null;
            }

            $cacheItem->set($content);
            $cacheItem->expiresAfter(60);

            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
