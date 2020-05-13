<?php

namespace Tests\App\RepublicanSilence\TagExtractor;

use App\Entity\Adherent;
use App\Entity\District;
use App\RepublicanSilence\TagExtractor\DistrictReferentTagExtractor;
use PHPUnit\Framework\TestCase;

class DistrictReferentTagExtractorTest extends TestCase
{
    public function testExtractTags()
    {
        $tagExtractor = new DistrictReferentTagExtractor();

        $deputyParisDistrictMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedDistrict' => $this->createConfiguredMock(District::class, [
                    'isFrenchDistrict' => true,
                    'getDepartmentCode' => '75',
                    'getCode' => '75001',
                ]),
        ]);

        $deputyCorseDistrictMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedDistrict' => $this->createConfiguredMock(District::class, [
                    'isFrenchDistrict' => true,
                    'getDepartmentCode' => '2A',
                ]),
        ]);

        $deputyFrenchDistrictMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedDistrict' => $this->createConfiguredMock(District::class, [
                    'isFrenchDistrict' => true,
                    'getDepartmentCode' => '92',
                ]),
        ]);

        $deputyForeignDistrictMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedDistrict' => $this->createConfiguredMock(District::class, [
                    'isFrenchDistrict' => false,
                    'getCountries' => ['CH', 'LI'],
                ]),
        ]);

        $this->assertSame(
            ['FR', '75', '75001', '75002', '75008', '75009'],
            $tagExtractor->extractTags($deputyParisDistrictMock, null)
        );

        $this->assertSame(
            ['FR', '2A'],
            $tagExtractor->extractTags($deputyCorseDistrictMock, null)
        );

        $this->assertSame(
            ['FR', '92'],
            $tagExtractor->extractTags($deputyFrenchDistrictMock, null)
        );

        $this->assertSame(
            ['CH', 'LI'],
            $tagExtractor->extractTags($deputyForeignDistrictMock, null)
        );
    }
}
