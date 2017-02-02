<?php

namespace AppBundle\Asset;

use AppBundle\Geocoder\Coordinates;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class GoogleMapsStaticProvider
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

    /**
     * @param Coordinates $coordinates
     *
     * @return string|false
     *
     * @throws \Exception
     */
    public function get(Coordinates $coordinates)
    {
        $id = self::CACHE_KEY_PREFIX.md5(serialize($coordinates));
        $item = $this->cache->getItem($id);

        // An image should always have contents
        if ($item->isHit() && $item->get()) {
            return $item->get();
        }

        if (!$contents = $this->fetch($coordinates)) {
            return false;
        }

        $item->set($contents);
        $this->cache->save($item);

        return $contents;
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return string|false
     */
    private function fetch(Coordinates $coordinates)
    {
        $parameters = http_build_query([
            'center' => $coordinates->getLatitude().','.$coordinates->getLongitude(),
            'zoom' => self::MAPS_ZOOM_LEVEL,
            'size' => '400x400',
            'key' => $this->key,
        ], null, '&', PHP_QUERY_RFC3986);

        try {
            $client = $this->client->request('GET', self::MAPS_API_ENDPOINT.'?'.$parameters);

            if (200 !== $client->getStatusCode()) {
                throw new \Exception(sprintf(
                    'Guzzle client status error: "%s" was returned with the reason "%s"',
                    $client->getStatusCode(),
                    $client->getReasonPhrase()
                ));
            }

            return $client->getBody()->getContents();
        } catch (\Exception $e) {
            $this->logger->error('GoogleMapsStaticProvider was unable to retrieve the map: '.$e->getMessage());
        }

        return false;
    }
}
