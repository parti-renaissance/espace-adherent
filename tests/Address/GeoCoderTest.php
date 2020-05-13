<?php

namespace Tests\App\Address;

use App\Address\GeoCoder;
use GeoIp2\Exception\GeoIp2Exception;
use GeoIp2\Model\City;
use GeoIp2\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group address
 */
class GeoCoderTest extends TestCase
{
    public function testCountryIsoCodeIsReturnedWhenIpHasBeenLocated(): void
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('city')
            ->with('123.123.123.123')
            ->willReturn(new City(['country' => ['iso_code' => 'FR']]))
        ;

        $geocoder = new GeoCoder($providerMock, $this->createMock(LoggerInterface::class));

        static::assertSame('FR', $geocoder->getCountryCodeFromIp('123.123.123.123'));
    }

    public function testLogWarningIfCanNotLocateIpAddress(): void
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('city')
            ->with('123.123.123.123')
            ->willThrowException(new GeoIp2Exception('geoip2 error'))
        ;

        $loggerProvider = $this->createMock(LoggerInterface::class);
        $loggerProvider->expects($this->once())->method('warning')->with('[GeoIP2] Unable to locate IP [123.123.123.123]: geoip2 error');

        $geocoder = new GeoCoder($providerMock, $loggerProvider);

        static::assertNull($geocoder->getCountryCodeFromIp('123.123.123.123'));
    }

    public function testLocationTimeZoneIsReturnedWhenIpHasBeenLocated(): void
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('city')
            ->with('123.123.123.123')
            ->willReturn(new City(['location' => ['time_zone' => 'Europe/Zurich']]))
        ;

        $geocoder = new GeoCoder($providerMock, $this->createMock(LoggerInterface::class));

        static::assertSame('Europe/Zurich', $geocoder->getTimezoneFromIp('123.123.123.123'));
    }

    public function testLocationTimeZoneNullIsReturnedWhenIpHasBeenLocated(): void
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('city')
            ->with('123.123.123.123')
            ->willReturn(new City(['location' => ['time_zone' => null]]))
        ;

        $geocoder = new GeoCoder($providerMock, $this->createMock(LoggerInterface::class));

        static::assertSame(GeoCoder::DEFAULT_TIME_ZONE, $geocoder->getTimezoneFromIp('123.123.123.123'));
    }

    public function testLogWarningIfCanNotLocateIpAddressForGetTimeZone(): void
    {
        $providerMock = $this->createMock(ProviderInterface::class);
        $providerMock->expects($this->once())
            ->method('city')
            ->with('123.123.123.123')
            ->willThrowException(new GeoIp2Exception('geoip2 error'))
        ;

        $loggerProvider = $this->createMock(LoggerInterface::class);
        $loggerProvider->expects($this->once())->method('warning')->with('[GeoIP2] Unable to locate IP [123.123.123.123]: geoip2 error');

        $geocoder = new GeoCoder($providerMock, $loggerProvider);

        static::assertSame(GeoCoder::DEFAULT_TIME_ZONE, $geocoder->getTimezoneFromIp('123.123.123.123'));
    }
}
