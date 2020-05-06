<?php

namespace Tests\App\Command;

use App\Entity\Event;
use App\Entity\Timeline\Theme;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group command
 */
class AlgoliaSynchronizeCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider dataProviderTestCommand
     */
    public function testCommand(array $parameters, array $expectedOutputs)
    {
        $output = $this->runCommand('app:algolia:synchronize', $parameters);

        foreach ($expectedOutputs as $expectedOutput) {
            $this->assertContains($expectedOutput, $output);
        }
    }

    public function dataProviderTestCommand(): array
    {
        return [
            [
                ['entityName' => Event::class],
                ['Synchronizing entity App\Entity\Event ... done, 21 records indexed'],
            ],
            [
                ['entityName' => Theme::class],
                ['Synchronizing entity App\Entity\Timeline\Theme ... done, 5 records indexed'],
            ],
            [
                [], // no parameters
                [
                    'Synchronizing entity App\Entity\Article ... done, 180 records indexed',
                    'Synchronizing entity App\Entity\Proposal ... done, 3 records indexed',
                    'Synchronizing entity App\Entity\Clarification ... done, 21 records indexed',
                    'Synchronizing entity App\Entity\CustomSearchResult ... done, 2 records indexed',
                    'Synchronizing entity App\Entity\Event ... done, 21 records indexed',
                    'Synchronizing entity App\Entity\Timeline\Profile ... done, 5 records indexed',
                    'Synchronizing entity App\Entity\Timeline\Manifesto ... done, 3 records indexed',
                    'Synchronizing entity App\Entity\Timeline\Theme ... done, 5 records indexed',
                    'Synchronizing entity App\Entity\Timeline\Measure ... done, 17 records indexed',
                ],
            ],
        ];
    }
}
