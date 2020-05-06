<?php

namespace Tests\App\RepublicanSilence\TagExtractor;

use App\Entity\Adherent;
use App\Entity\ReferentManagedArea;
use App\Entity\ReferentTag;
use App\RepublicanSilence\TagExtractor\ReferentTagExtractor;
use PHPUnit\Framework\TestCase;

class ReferentTagExtractorTest extends TestCase
{
    public function testExtractTags()
    {
        $tagExtractor = new ReferentTagExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getManagedArea' => new ReferentManagedArea([
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
