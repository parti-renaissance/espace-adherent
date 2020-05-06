<?php

namespace Tests\App\RepublicanSilence\TagExtractor;

use App\Collection\CommitteeMembershipCollection;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\RepublicanSilence\TagExtractor\CommitteeReferentTagExtractor;
use PHPUnit\Framework\TestCase;

class CommitteeReferentTagExtractorTest extends TestCase
{
    public function testExtractTags()
    {
        $tagExtractor = new CommitteeReferentTagExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getMemberships' => new CommitteeMembershipCollection([
                $this->createConfiguredMock(CommitteeMembership::class, [
                    'isHostMember' => true,
                    'getCommittee' => $this->createConfiguredMock(Committee::class, [
                        'getSlug' => 'committee-slug',
                        'getReferentTagsCodes' => ['tag1', 'tag2', 'tag10'],
                    ]),
                ]),
            ]),
        ]);

        $this->assertSame(
            ['tag1', 'tag2', 'tag10'],
            $tagExtractor->extractTags($adherentMock, 'committee-slug')
        );
    }
}
