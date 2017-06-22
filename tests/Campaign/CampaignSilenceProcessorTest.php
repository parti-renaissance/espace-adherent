<?php

namespace Tests\AppBundle\Campaign;

use AppBundle\Campaign\CampaignSilenceProcessor;
use GeoIp2\Database\Reader;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;

class CampaignSilenceProcessorTest extends TestCase
{
    const IP_AU_SYDNEY = '45.63.31.236';
    const IP_CA_TORONTO = '159.203.18.38';
    const IP_CN_HANGZHOU = '112.124.45.3';
    const IP_DE_FRANKFURT = '46.101.102.25';
    const IP_IN_BENGALURU = '139.59.10.158';
    const IP_NL_AMSTERDAM = '146.185.134.28';
    const IP_SG_SINGAPORE = '188.166.231.109';
    const IP_GB_LONDON = '139.59.161.91';
    const IP_US_SAN_FRANCISCO = '104.236.132.36';
    const IP_US_NEW_YORK = '198.199.75.19';
    const IP_FR_PARIS = '88.190.229.170';
    const IP_GP_SAINTE_ANNE = '199.101.189.234';
    const IP_RE_SAINTE_CLOTILDE = '80.12.212.10';

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function setUp()
    {
        $this->cache = new ArrayAdapter();
    }

    public function tearDown()
    {
        $this->cache = null;
    }

    public function testNotSilentAndCacheIP()
    {
        $processor = $this->createProcessor('false');

        $this->assertAccessible($processor, self::IP_FR_PARIS);

        $item = $this->cache->getItem('silence_ip_'.md5(self::IP_FR_PARIS));
        $this->assertTrue($item->isHit());
        $this->assertSame(['code' => 'FR', 'in_america' => false], $item->get());
    }

    public function testWhitelist()
    {
        $processor = $this->createProcessor('country.code not in ["FR", "GP", "RE"]');

        $this->assertExpired($processor, self::IP_AU_SYDNEY);
        $this->assertExpired($processor, self::IP_CA_TORONTO);
        $this->assertExpired($processor, self::IP_CN_HANGZHOU);
        $this->assertExpired($processor, self::IP_DE_FRANKFURT);
        $this->assertExpired($processor, self::IP_IN_BENGALURU);
        $this->assertExpired($processor, self::IP_NL_AMSTERDAM);
        $this->assertExpired($processor, self::IP_SG_SINGAPORE);
        $this->assertExpired($processor, self::IP_GB_LONDON);
        $this->assertExpired($processor, self::IP_US_SAN_FRANCISCO);
        $this->assertExpired($processor, self::IP_US_NEW_YORK);
        $this->assertAccessible($processor, self::IP_FR_PARIS);
        $this->assertAccessible($processor, self::IP_GP_SAINTE_ANNE);
        $this->assertAccessible($processor, self::IP_RE_SAINTE_CLOTILDE);
    }

    public function testBlacklist()
    {
        $processor = $this->createProcessor('country.code in ["US", "IN"]');

        $this->assertAccessible($processor, self::IP_AU_SYDNEY);
        $this->assertAccessible($processor, self::IP_CA_TORONTO);
        $this->assertAccessible($processor, self::IP_CN_HANGZHOU);
        $this->assertAccessible($processor, self::IP_DE_FRANKFURT);
        $this->assertExpired($processor, self::IP_IN_BENGALURU);
        $this->assertAccessible($processor, self::IP_NL_AMSTERDAM);
        $this->assertAccessible($processor, self::IP_SG_SINGAPORE);
        $this->assertAccessible($processor, self::IP_GB_LONDON);
        $this->assertExpired($processor, self::IP_US_SAN_FRANCISCO);
        $this->assertExpired($processor, self::IP_US_NEW_YORK);
        $this->assertAccessible($processor, self::IP_FR_PARIS);
        $this->assertAccessible($processor, self::IP_GP_SAINTE_ANNE);
        $this->assertAccessible($processor, self::IP_RE_SAINTE_CLOTILDE);
    }

    public function testAmerica()
    {
        $processor = $this->createProcessor('country.in_america');

        $this->assertAccessible($processor, self::IP_AU_SYDNEY);
        $this->assertExpired($processor, self::IP_CA_TORONTO);
        $this->assertAccessible($processor, self::IP_CN_HANGZHOU);
        $this->assertAccessible($processor, self::IP_DE_FRANKFURT);
        $this->assertAccessible($processor, self::IP_IN_BENGALURU);
        $this->assertAccessible($processor, self::IP_NL_AMSTERDAM);
        $this->assertAccessible($processor, self::IP_SG_SINGAPORE);
        $this->assertAccessible($processor, self::IP_GB_LONDON);
        $this->assertExpired($processor, self::IP_US_SAN_FRANCISCO);
        $this->assertExpired($processor, self::IP_US_NEW_YORK);
        $this->assertAccessible($processor, self::IP_FR_PARIS);
        $this->assertExpired($processor, self::IP_GP_SAINTE_ANNE);
        $this->assertAccessible($processor, self::IP_RE_SAINTE_CLOTILDE);
    }

    public function testBlacklistAndAmerica()
    {
        $processor = $this->createProcessor('country.code in ["DE", "NL"] or country.in_america');

        $this->assertAccessible($processor, self::IP_AU_SYDNEY);
        $this->assertExpired($processor, self::IP_CA_TORONTO);
        $this->assertAccessible($processor, self::IP_CN_HANGZHOU);
        $this->assertExpired($processor, self::IP_DE_FRANKFURT);
        $this->assertAccessible($processor, self::IP_IN_BENGALURU);
        $this->assertExpired($processor, self::IP_NL_AMSTERDAM);
        $this->assertAccessible($processor, self::IP_SG_SINGAPORE);
        $this->assertAccessible($processor, self::IP_GB_LONDON);
        $this->assertExpired($processor, self::IP_US_SAN_FRANCISCO);
        $this->assertExpired($processor, self::IP_US_NEW_YORK);
        $this->assertAccessible($processor, self::IP_FR_PARIS);
        $this->assertExpired($processor, self::IP_GP_SAINTE_ANNE);
        $this->assertAccessible($processor, self::IP_RE_SAINTE_CLOTILDE);
    }

    public function testFullSilent()
    {
        $processor = $this->createProcessor('true');

        $this->assertExpired($processor, self::IP_AU_SYDNEY);
        $this->assertExpired($processor, self::IP_CA_TORONTO);
        $this->assertExpired($processor, self::IP_CN_HANGZHOU);
        $this->assertExpired($processor, self::IP_DE_FRANKFURT);
        $this->assertExpired($processor, self::IP_IN_BENGALURU);
        $this->assertExpired($processor, self::IP_NL_AMSTERDAM);
        $this->assertExpired($processor, self::IP_SG_SINGAPORE);
        $this->assertExpired($processor, self::IP_GB_LONDON);
        $this->assertExpired($processor, self::IP_US_SAN_FRANCISCO);
        $this->assertExpired($processor, self::IP_US_NEW_YORK);
        $this->assertExpired($processor, self::IP_FR_PARIS);
        $this->assertExpired($processor, self::IP_GP_SAINTE_ANNE);
        $this->assertExpired($processor, self::IP_RE_SAINTE_CLOTILDE);
    }

    private function createProcessor(string $rule)
    {
        return new CampaignSilenceProcessor(
            new Reader(__DIR__.'/../../app/data/geolite2-countries.mmdb'),
            new ExpressionLanguage(),
            $this->cache,
            $rule
        );
    }

    private function assertExpired(CampaignSilenceProcessor $processor, string $ip)
    {
        $this->assertTrue(
            $processor->isCampaignExpired($this->createRequest($ip)),
            'The website should be expired for IP '.$ip
        );
    }

    private function assertAccessible(CampaignSilenceProcessor $processor, string $ip)
    {
        $this->assertFalse(
            $processor->isCampaignExpired($this->createRequest($ip)),
            'The website should be accessible for IP '.$ip
        );
    }

    private function createRequest(string $ip, bool $routeEnableCampaignSilence = false)
    {
        $attributes = [
            '_controller' => 'AppBundle\Controller\HomeController::indexAction',
            '_route' => 'homepage',
            '_route_params' => [
                '_enable_campaign_silence' => $routeEnableCampaignSilence,
            ],
        ];

        $server = [
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];

        return new Request([], [], $attributes, [], [], $server);
    }
}
