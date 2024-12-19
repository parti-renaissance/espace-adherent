<?php

namespace Tests\App\RepublicanSilence\TagExtractor;

use App\Collection\ZoneCollection;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\RepublicanSilence\ZoneExtractor\CommitteeZoneExtractor;
use PHPUnit\Framework\TestCase;

class CommitteeZoneExtractorTest extends TestCase
{
    public function testExtractZones()
    {
        $tagExtractor = new CommitteeZoneExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getCommitteeMembership' => $this->createConfiguredMock(CommitteeMembership::class, [
                'isHostMember' => true,
                'getCommittee' => $this->createConfiguredMock(Committee::class, [
                    'getSlug' => 'committee-slug',
                    'getZones' => new ZoneCollection([
                        $zone1 = new Zone('mock', 'tag1', ''),
                        $zone2 = new Zone('mock', 'tag2', ''),
                    ]),
                ]),
            ]),
        ]);

        $this->assertSame(
            [$zone1, $zone2],
            $tagExtractor->extractZones($adherentMock, 'committee-slug')
        );
    }
}
