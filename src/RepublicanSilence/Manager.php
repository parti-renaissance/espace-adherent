<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Entity\RepublicanSilence;
use AppBundle\Repository\RepublicanSilenceRepository;
use Psr\SimpleCache\CacheInterface;

class Manager
{
    private const CACHE_PREFIX = 'republican_silence_';

    private $repository;
    private $cache;

    public function __construct(RepublicanSilenceRepository $repository, CacheInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    /**
     * @return RepublicanSilence[]|iterable
     */
    public function getRepublicanSilenceForDate(\DateTime $date): iterable
    {
        $cacheKey = $this->getCacheKey($date);

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $silences = $this->repository->findStarted($date);

        $this->cache->set($cacheKey, $silences, 86400); // with ttl: 24 H

        return $silences;
    }

    public function hasStartedSilence(array $userZones): bool
    {
        $silences = $this->getRepublicanSilenceForDate(new \DateTime());

        foreach ($silences as $silence) {
            if (array_intersect($silence->getZones(), $userZones)) {
                return true;
            }
        }

        return false;
    }

    public function clearCache(\DateTime $date): bool
    {
        return $this->cache->delete($this->getCacheKey($date));
    }

    private function getCacheKey(\DateTime $date): string
    {
        return self::CACHE_PREFIX.$date->format('d-m-Y');
    }
}
