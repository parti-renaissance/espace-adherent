<?php

namespace Tests\App\Command;

use App\Entity\ChezVous\City;
use App\Entity\CustomSearchResult;
use App\Entity\Proposal;
use App\Entity\Timeline\Manifesto;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Profile;
use App\Entity\Timeline\Theme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('command')]
class AlgoliaSynchronizeCommandTest extends AbstractCommandTestCase
{
    #[DataProvider('dataProviderTestCommand')]
    public function testCommand(string $indexName, string $className, int $expected)
    {
        $output = $this->runCommand('search:import', ['--indices' => $indexName]);

        $this->assertStringContainsString('Done!', $output->getDisplay());

        $indexer = $this->get('search.service');
        self::assertSame($expected, $indexer->countForIndexByType($className));
    }

    public static function dataProviderTestCommand(): array
    {
        return [
            ['proposal', Proposal::class, 3],
            ['custom_search_result', CustomSearchResult::class, 2],
            // Timeline
            ['timeline_theme', Theme::class, 5],
            ['timeline_profile', Profile::class, 5],
            ['timeline_manifesto', Manifesto::class, 3],
            ['timeline_measure', Measure::class, 17],
            // ChezVous
            ['chezvous_city', City::class, 3],
        ];
    }
}
