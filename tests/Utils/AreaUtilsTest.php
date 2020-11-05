<?php

namespace Tests\App\Utils;

use App\Entity\EntityPostAddressInterface;
use App\Utils\AreaUtils;
use PHPUnit\Framework\TestCase;

class AreaUtilsTest extends TestCase
{
    /**
     * @dataProvider providePostalCodes
     */
    public function testGetCodeFromPostalCode($postalCode, $expectedCode): void
    {
        $this->assertEquals($expectedCode, AreaUtils::getCodeFromPostalCode($postalCode));
    }

    public function providePostalCodes(): \Generator
    {
        yield ['59000', '59'];
        yield ['77000', '77'];
        yield ['92500', '92'];
        yield ['20100', '2A'];
        yield ['20220', '2B'];
        yield ['75008', '75008'];
        yield ['97427', '974'];
        yield ['98890', '988'];
        yield ['98000', 'MC'];
    }

    /**
     * @dataProvider provideCountries
     */
    public function testGetCodeFromCountry($country, $expectedCode): void
    {
        $this->assertEquals($expectedCode, AreaUtils::getCodeFromCountry($country));
    }

    public function provideCountries(): \Generator
    {
        yield ['DE', 'DE'];
        yield ['IT', 'IT'];
        yield ['CH', 'CH'];
        yield ['MC', 'MC'];
        yield ['unknown', 'unknown'];
    }

    /**
     * @dataProvider provideRelatedCodes
     */
    public function testGetRelatedCodes(string $code, $expectedRelatedCodes): void
    {
        $this->assertEquals($expectedRelatedCodes, AreaUtils::getRelatedCodes($code));
    }

    public function provideRelatedCodes(): \Generator
    {
        yield ['75001', ['75']];
        yield ['75002', ['75']];
        yield ['75014', ['75']];
        yield ['75015', ['75']];
        yield ['75020', ['75']];
        yield ['2A', ['20']];
        yield ['2B', ['20']];
        yield ['59', []];
        yield ['06', []];
    }

    /**
     * @dataProvider provideMetropolis
     */
    public function testGetMetropolisCode(?string $expectedCode, string $country, string $inseeCode): void
    {
        $entity = new class($country, $inseeCode) implements EntityPostAddressInterface {
            public $country;
            public $inseeCode;

            public function __construct(string $country, string $inseeCode)
            {
                $this->country = $country;
                $this->inseeCode = $inseeCode;
            }

            public function getCountry(): ?string
            {
                return $this->country;
            }

            public function getPostalCode(): ?string
            {
                return '11111';
            }

            public function getInseeCode(): ?string
            {
                return $this->inseeCode;
            }
        };

        $this->assertEquals($expectedCode, AreaUtils::getMetropolisCode($entity));
    }

    public function provideMetropolis(): \Generator
    {
        yield ['34M', 'FR', '34058'];
        yield ['34M', 'FR', '34172'];
        yield ['69M', 'FR', '69123'];
        yield ['69M', 'FR', '69003'];
        yield [null, 'FR', '75056'];
        yield [null, 'CH', '57340'];
    }

    /**
     * @dataProvider provideZones
     */
    public function testGetZone(string $country, ?string $inseeCode, string $expectedZone): void
    {
        $entity = new class($country, $inseeCode) implements EntityPostAddressInterface {
            public $country;
            public $inseeCode;

            public function __construct(string $country, ?string $inseeCode)
            {
                $this->country = $country;
                $this->inseeCode = $inseeCode;
            }

            public function getCountry(): ?string
            {
                return $this->country;
            }

            public function getPostalCode(): ?string
            {
                return '11111';
            }

            public function getInseeCode(): ?string
            {
                return $this->inseeCode;
            }
        };

        $this->assertEquals($expectedZone, AreaUtils::getZone($entity));
    }

    public function provideZones(): \Generator
    {
        yield ['CH', null, 'CH'];
        yield ['FR', '75010', '75010'];
        yield ['FR', '6059', '06059'];
    }
}
