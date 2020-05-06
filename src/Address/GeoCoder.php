<?php

namespace App\Address;

use GeoIp2\Exception\GeoIp2Exception;
use GeoIp2\Model\City;
use GeoIp2\ProviderInterface;
use Psr\Log\LoggerInterface;

class GeoCoder
{
    public const DEFAULT_TIME_ZONE = 'Europe/Paris';
    private $provider;
    private $logger;
    /**
     * @var City[]|null
     */
    private $cacheRecord = [];

    public function __construct(ProviderInterface $provider, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    public function getCountryCodeFromIp(string $ip): ?string
    {
        $record = $this->getGeoDataByIp($ip);
        if (null !== $record) {
            return $record->country->isoCode;
        }

        return null;
    }

    public function getTimezoneFromIp(string $ip): string
    {
        $record = $this->getGeoDataByIp($ip);
        if (null !== $record) {
            return $record->location->timeZone ?? self::DEFAULT_TIME_ZONE;
        }

        return self::DEFAULT_TIME_ZONE;
    }

    private function getGeoDataByIp(string $ip): ?City
    {
        try {
            if (empty($this->cacheRecord[$ip])) {
                $this->cacheRecord[$ip] = $this->provider->city($ip);
            }

            return $this->cacheRecord[$ip];
        } catch (GeoIp2Exception $e) {
            $this->logger->warning(sprintf('[GeoIP2] Unable to locate IP [%s]: %s', $ip, $e->getMessage()), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }
}
