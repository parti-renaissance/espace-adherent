<?php

declare(strict_types=1);

namespace Tests\App\Utils;

use App\Entity\EntityPostAddressInterface;
use App\Utils\AreaUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AreaUtilsTest extends TestCase
{
    #[DataProvider('providePostalCodes')]
    public function testGetCodeFromPostalCode($postalCode, $expectedCode): void
    {
        $this->assertEquals($expectedCode, AreaUtils::getCodeFromPostalCode($postalCode));
    }

    public static function providePostalCodes(): \Generator
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

    #[DataProvider('provideCountries')]
    public function testGetCodeFromCountry($country, $expectedCode): void
    {
        $this->assertEquals($expectedCode, AreaUtils::getCodeFromCountry($country));
    }

    public static function provideCountries(): \Generator
    {
        yield ['DE', 'DE'];
        yield ['IT', 'IT'];
        yield ['CH', 'CH'];
        yield ['MC', 'MC'];
        yield ['unknown', 'unknown'];
    }

    #[DataProvider('provideRelatedCodes')]
    public function testGetRelatedCodes(string $code, $expectedRelatedCodes): void
    {
        $this->assertEquals($expectedRelatedCodes, AreaUtils::getRelatedCodes($code));
    }

    public static function provideRelatedCodes(): \Generator
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

    #[DataProvider('provideMetropolis')]
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

    #[DataProvider('provideCodes')]
    public function testGet69DCode(?string $expectedCode, string $country, string $postalCode, string $inseeCode): void
    {
        $entity = new class($country, $inseeCode, $postalCode) implements EntityPostAddressInterface {
            public $country;
            public $inseeCode;
            public $postalCode;

            public function __construct(string $country, string $inseeCode, string $postalCode)
            {
                $this->country = $country;
                $this->inseeCode = $inseeCode;
                $this->postalCode = $postalCode;
            }

            public function getCountry(): ?string
            {
                return $this->country;
            }

            public function getPostalCode(): ?string
            {
                return $this->postalCode;
            }

            public function getInseeCode(): ?string
            {
                return $this->inseeCode;
            }
        };

        $this->assertEquals($expectedCode, AreaUtils::get69DCode($entity));
    }

    public static function provideMetropolis(): \Generator
    {
        yield ['34M', 'FR', '34058'];
        yield ['34M', 'FR', '34172'];
        yield ['69M', 'FR', '69123'];
        yield ['69M', 'FR', '69003'];
        yield [null, 'FR', '75056'];
        yield [null, 'CH', '57340'];
    }

    public static function provideCodes(): \Generator
    {
        yield [null, 'FR', '34160', '34058'];
        yield [null, 'FR', '69001', '69123'];
        yield [null, 'FR', '69266', '69100'];
        yield ['69D', 'FR', '69440', '69051'];
        yield [null, 'FR', '75001', '75056'];
        yield [null, 'CH', '57340', '57662'];
    }

    #[DataProvider('provideZones')]
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

    public static function provideZones(): \Generator
    {
        yield ['CH', null, 'CH'];
        yield ['FR', '75010', '75010'];
        yield ['FR', '6059', '06059'];
    }
}
