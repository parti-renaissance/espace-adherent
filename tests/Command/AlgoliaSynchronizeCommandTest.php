<?php

declare(strict_types=1);

namespace Tests\App\Command;

use Algolia\SearchBundle\SearchService;
use App\Entity\CustomSearchResult;
use App\Entity\Proposal;
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

        $indexer = $this->get(SearchService::class);
        self::assertSame($expected, $indexer->countForIndexByType($className));
    }

    public static function dataProviderTestCommand(): array
    {
        return [
            ['proposal', Proposal::class, 3],
            ['custom_search_result', CustomSearchResult::class, 2],
        ];
    }
}
