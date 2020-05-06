<?php

namespace App\Map;

use App\Geocoder\Coordinates;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class GoogleStaticMapProvider implements StaticMapProviderInterface
{
    const MAPS_ZOOM_LEVEL = 15;
    const MAPS_API_ENDPOINT = '/maps/api/staticmap';
    const CACHE_KEY_PREFIX = 'maps_static_';

    private $client;
    private $cache;
    private $logger;
    private $key;

    public function __construct(Client $client, AdapterInterface $cache, LoggerInterface $logger, string $key)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->key = $key;
    }

    public function get(Coordinates $coordinates, ?string $size = null)
    {
        $id = self::CACHE_KEY_PREFIX.md5($coordinates->getLatitude().'-'.$coordinates->getLongitude().'_'.$size);
        $item = $this->cache->getItem($id);

        if (!$item->isHit()) {
            if (!$contents = $this->fetch($coordinates, $size ?: '400x400')) {
                return false;
            }

            $item->set($contents);
            $this->cache->save($item);
        }

        return $item->get();
    }

    private function fetch(Coordinates $coordinates, string $size)
    {
        $parameters = http_build_query([
            'center' => $coordinates->getLatitude().','.$coordinates->getLongitude(),
            'zoom' => self::MAPS_ZOOM_LEVEL,
            'size' => $size,
            'key' => $this->key,
            'markers' => $coordinates->getLatitude().','.$coordinates->getLongitude(),
        ], null, '&', \PHP_QUERY_RFC3986);

        try {
            return $this->client->request('GET', self::MAPS_API_ENDPOINT.'?'.$parameters)->getBody()->getContents();
        } catch (\Exception $e) {
            $this->logger->warning('Unable to retrieve the map', ['exception' => $e]);
        }

        return false;
    }
}
