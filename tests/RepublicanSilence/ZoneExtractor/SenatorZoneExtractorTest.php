<?php

namespace Tests\App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
use App\Entity\SenatorArea;
use App\RepublicanSilence\ZoneExtractor\SenatorZoneExtractor;
use PHPUnit\Framework\TestCase;

class SenatorZoneExtractorTest extends TestCase
{
    public function testExtractZones()
    {
        $tagExtractor = new SenatorZoneExtractor();
        $senatorArea = new SenatorArea();
        $senatorArea->setDepartmentTag(
            new ReferentTag(null, 'dpt1', $zone1 = new Zone('mock', 'dpt1', ''))
        );

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getSenatorArea' => $senatorArea,
        ]);

        $this->assertSame(
            [$zone1],
            $tagExtractor->extractZones($adherentMock, null)
        );
    }
}
