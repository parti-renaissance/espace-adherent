<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Address\Address;
use AppBundle\Entity\ReferentTag;
use AppBundle\Entity\RepublicanSilence;
use AppBundle\Repository\ReferentTagRepository;
use AppBundle\Repository\RepublicanSilenceRepository;
use Psr\SimpleCache\CacheInterface;

class RepublicanSilenceManager
{
    private const CACHE_PREFIX_KEY = 'republican_silence_';

    private $repository;
    private $cache;

    public function __construct(RepublicanSilenceRepository $repository, CacheInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesForDate(\DateTimeInterface $date): iterable
    {
        $cacheKey = $this->getCacheKey($date);

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $silences = $this->repository->findStarted($date);

        $this->cache->set($cacheKey, $silences, 86400); // with ttl: 24 H

        return $silences;
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesFromDate(\DateTimeInterface $date): iterable
    {
        return $this->repository->findFromDate($date);
    }

    public function hasStartedSilence(array $referentTagCodes = null): bool
    {
        $silences = $this->getRepublicanSilencesForDate(new \DateTime());

        if (null === $referentTagCodes) {
            return !empty($silences);
        }

        foreach ($silences as $silence) {
            if ($this->matchSilence($silence, $referentTagCodes)) {
                return true;
            }
        }

        return false;
    }

    public function clearCache(\DateTimeInterface $date): bool
    {
        return $this->cache->delete($this->getCacheKey($date));
    }

    private function getCacheKey(\DateTimeInterface $date): string
    {
        return self::CACHE_PREFIX_KEY.$date->format('d-m-Y');
    }

    private function matchSilence(RepublicanSilence $silence, array $referentTagCodes): bool
    {
        if (array_intersect($silence->getReferentTagCodes(), $referentTagCodes)) {
            return true;
        }

        if (
            \in_array(ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG, $referentTagCodes, true)
            && !$silence->getReferentTags()->filter(static function (ReferentTag $tag) {
                return ReferentTag::TYPE_COUNTRY === $tag->getType()
                    && Address::FRANCE !== $tag->getCode();
            })->isEmpty()
        ) {
            return true;
        }

        return false;
    }
}
