<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\MunicipalSite\ApiDriver;
use Psr\SimpleCache\CacheInterface;

class MunicipalSiteRuntime
{
    private const CACHE_KEY_PATTERN = 'municipal_site_%s';

    private $apiDriver;
    private $cache;

    public function __construct(ApiDriver $apiDriver, CacheInterface $cache)
    {
        $this->apiDriver = $apiDriver;
        $this->cache = $cache;
    }

    public function isMunicipalSiteEnabled(Adherent $user): bool
    {
        if (false === $user->isMunicipalChief()) {
            throw new \RuntimeException('Adherent should be municipal chief');
        }

        $cacheKey = sprintf(
            self::CACHE_KEY_PATTERN,
            $inseeCode = $user->getMunicipalChiefManagedArea()->getInseeCode()
        );

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey, false);
        }

        if ($response = $this->apiDriver->isMunicipalSiteEnabled($inseeCode)) {
            $this->cache->set($cacheKey, $response, new \DateInterval('PT60M'));
        } else {
            $this->cache->set($cacheKey, $response, new \DateInterval('PT5M'));
        }

        return $response;
    }
}
