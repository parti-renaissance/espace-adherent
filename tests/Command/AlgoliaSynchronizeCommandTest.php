<?php

namespace Tests\AppBundle\Command;

use AppBundle\Entity\Event;
use AppBundle\Entity\Timeline\Theme;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

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
                ['Synchronizing entity AppBundle\Entity\Event ... done, 21 records indexed'],
            ],
            [
                ['entityName' => Theme::class],
                ['Synchronizing entity AppBundle\Entity\Timeline\Theme ... done, 5 records indexed'],
            ],
            [
                [], // no parameters
                [
                    'Synchronizing entity AppBundle\Entity\Article ... done, 180 records indexed',
                    'Synchronizing entity AppBundle\Entity\Proposal ... done, 3 records indexed',
                    'Synchronizing entity AppBundle\Entity\Clarification ... done, 21 records indexed',
                    'Synchronizing entity AppBundle\Entity\CustomSearchResult ... done, 2 records indexed',
                    'Synchronizing entity AppBundle\Entity\Event ... done, 21 records indexed',
                    'Synchronizing entity AppBundle\Entity\Timeline\Profile ... done, 5 records indexed',
                    'Synchronizing entity AppBundle\Entity\Timeline\Manifesto ... done, 3 records indexed',
                    'Synchronizing entity AppBundle\Entity\Timeline\Theme ... done, 5 records indexed',
                    'Synchronizing entity AppBundle\Entity\Timeline\Measure ... done, 17 records indexed',
                ],
            ],
        ];
    }
}
