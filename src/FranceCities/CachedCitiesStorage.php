<?php

declare(strict_types=1);

namespace App\FranceCities;

use Psr\SimpleCache\CacheInterface;

class CachedCitiesStorage implements CitiesStorageInterface
{
    private const CACHE_KEY = 'france_cities';

    private CitiesStorageInterface $decorated;
    private CacheInterface $cache;

    public function __construct(CitiesStorageInterface $decorated, CacheInterface $cache)
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    public function getCitiesList(): array
    {
        if (!$this->cache->has(self::CACHE_KEY)) {
            $franceCities = $this->decorated->getCitiesList();

            $this->cache->set(self::CACHE_KEY, $franceCities, 86400);
        } else {
            $franceCities = $this->cache->get(self::CACHE_KEY);
        }

        return $franceCities;
    }
}
