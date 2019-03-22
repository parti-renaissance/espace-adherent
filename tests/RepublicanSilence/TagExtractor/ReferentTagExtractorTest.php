<?php

namespace Tests\AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentReferentData;
use AppBundle\Entity\ReferentTag;
use AppBundle\RepublicanSilence\TagExtractor\ReferentTagExtractor;
use PHPUnit\Framework\TestCase;

class ReferentTagExtractorTest extends TestCase
{
    public function testExtractTags()
    {
        $tagExtractor = new ReferentTagExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedArea' => new AdherentReferentData([
                new ReferentTag(null, 'tag1'),
                new ReferentTag(null, 'tag2'),
            ]),
        ]);

        $this->assertSame(
            ['tag1', 'tag2'],
            $tagExtractor->extractTags($adherentMock, null)
        );
    }
}
