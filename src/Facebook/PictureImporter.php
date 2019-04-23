<?php

namespace AppBundle\Facebook;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheItemPoolInterface;
use function GuzzleHttp\Psr7\copy_to_string;

class PictureImporter
{
    private $client;
    private $cache;

    public function __construct(ClientInterface $client, CacheItemPoolInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function import(int $facebookId): ?string
    {
        $cacheItem = $this->cache->getItem('fb_picture_'.$facebookId);

        if (!$cacheItem->isHit()) {
            try {
                $response = $this->client->request('GET', '/'.$facebookId.'/picture?width=1500&height=1500');
            } catch (GuzzleException $exception) {
                return null;
            }

            $cacheItem->set(copy_to_string($response->getBody()));
            $cacheItem->expiresAfter(60);

            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
