<?php

namespace AppBundle\Campaign;

use GeoIp2\Database\Reader;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;

class CampaignSilenceProcessor
{
    private $geoip;
    private $language;
    private $cache;
    private $enableSilenceRule;

    public function __construct(
        Reader $geoip,
        ExpressionLanguage $language,
        CacheItemPoolInterface $cache,
        string $enableSilenceRule
    ) {
        $this->geoip = $geoip;
        $this->language = $language;
        $this->cache = $cache;
        $this->enableSilenceRule = $enableSilenceRule;
    }

    public function isCampaignExpired(Request $request): bool
    {
        $ip = $this->getClientIp($request);
        $ipCache = $this->cache->getItem('silence_ip_'.md5($ip));

        if (!$ipCache->isHit()) {
            try {
                $code = $this->geoip->country($ip)->country->isoCode ?: 'US';
            } catch (\Exception $e) {
                $code = 'US';
            }

            $ipCache->set([
                'code' => $code,
                'in_america' => $this->isCountryInAmerica($code),
            ]);

            $this->cache->save($ipCache);
        }

        return $this->language->evaluate($this->enableSilenceRule, [
            'country' => (object) $ipCache->get(),
        ]);
    }

    private function getClientIp(Request $request)
    {
        $clientIp = $request->server->get('HTTP_CF_CONNECTING_IP');

        if ($clientIp) {
            return $clientIp;
        }

        $clientIp = $request->getClientIps();

        return end($clientIp);
    }

    private function isCountryInAmerica(string $country): bool
    {
        $lisbonTime = (new \DateTime('now', new \DateTimeZone('Europe/Lisbon')));

        foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country) as $timezone) {
            $clientTime = (new \DateTime('now', new \DateTimeZone($timezone)));

            if ((int) $clientTime->format('dHis') < (int) $lisbonTime->format('dHis')) {
                return true;
            }
        }

        return false;
    }
}
