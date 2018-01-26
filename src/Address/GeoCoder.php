<?php

namespace AppBundle\Address;

use GeoIp2\Exception\GeoIp2Exception;
use GeoIp2\ProviderInterface;
use Psr\Log\LoggerInterface;

class GeoCoder
{
    private $provider;
    private $logger;

    public function __construct(ProviderInterface $provider, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    public function getCountryCodeFromIp(string $ip): ?string
    {
        try {
            return $this->provider->country($ip)->country->isoCode;
        } catch (GeoIp2Exception $e) {
            $this->logger->warning(sprintf('[GeoIP2] Unable to locate IP [%s]: %s', $ip, $e->getMessage()), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }
}
