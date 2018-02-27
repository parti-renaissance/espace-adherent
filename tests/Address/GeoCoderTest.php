<?php

namespace Tests\AppBundle\Address;

use AppBundle\Address\GeoCoder;
use GeoIp2\Exception\GeoIp2Exception;
use GeoIp2\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GeoCoderTest extends TestCase
{
    public function testCountryIsoCodeIsReturnedWhenIpHasBeenLocated()
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('country')
            ->with('123.123.123.123')
            ->willReturn(json_decode(json_encode(['country' => ['isoCode' => 'FR']])))
        ;

        $geocoder = new GeoCoder($providerMock, $this->createMock(LoggerInterface::class));

        static::assertSame('FR', $geocoder->getCountryCodeFromIp('123.123.123.123'));
    }

    public function testLogWarningIfCanNotLocateIpAddress()
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('country')
            ->with('123.123.123.123')
            ->willThrowException(new GeoIp2Exception('geoip2 error'))
        ;

        $loggerProvider = $this->createMock(LoggerInterface::class);
        $loggerProvider->expects($this->once())->method('warning')->with('[GeoIP2] Unable to locate IP [123.123.123.123]: geoip2 error');

        $geocoder = new GeoCoder($providerMock, $loggerProvider);

        static::assertNull($geocoder->getCountryCodeFromIp('123.123.123.123'));
    }
}
