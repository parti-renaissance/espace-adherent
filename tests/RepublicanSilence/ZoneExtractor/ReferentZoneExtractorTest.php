<?php

namespace Tests\App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ReferentManagedArea;
use App\Entity\ReferentTag;
use App\RepublicanSilence\ZoneExtractor\ReferentZoneExtractor;
use PHPUnit\Framework\TestCase;

class ReferentZoneExtractorTest extends TestCase
{
    public function testExtractZones()
    {
        $tagExtractor = new ReferentZoneExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedArea' => new ReferentManagedArea([
                new ReferentTag(null, 'tag1', $zone1 = new Zone('mock', 'tag1', '')),
                new ReferentTag(null, 'tag2', $zone2 = new Zone('mock', 'tag2', '')),
            ]),
        ]);

        $this->assertSame(
            [$zone1, $zone2],
            $tagExtractor->extractZones($adherentMock, null)
        );
    }
}
