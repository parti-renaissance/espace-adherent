<?php

namespace Tests\App\MajorityJudgment;

use App\MajorityJudgment\Election;
use App\MajorityJudgment\Mention;
use App\MajorityJudgment\Processor;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @dataProvider getElectionData
     */
    public function testA(array $candidates, array $votingProfiles, string $expectedWinner): void
    {
        $election = Election::createWithVotingProfiles(
            [
                new Mention('Excellent'),
                new Mention('Good'),
                new Mention('Pretty good'),
                new Mention('Fair'),
                new Mention('Insufficient'),
                new Mention('To reject'),
            ],
            $candidates,
            $votingProfiles
        );

        Processor::process($election);

        self::assertSame($expectedWinner, $election->getWinner()->getIdentifier());
    }

    public function getElectionData(): \Generator
    {
        yield 'test 1: simple vote with 2 candidates and 2 votes' => [
            [
                'Mr A',
                'Mr B',
            ],
            [
                'Mr A' => [1, 1, 0, 0, 0, 0],
                'Mr B' => [0, 0, 0, 0, 1, 1],
            ],
            'Mr A',
        ];

        yield 'test 2: ...' => [
            [
                'Mr A',
                'Mr B',
            ],
            [
                'Mr A' => [1, 1, 0, 0, 0, 0],
                'Mr B' => [0, 1, 1, 0, 0, 0],
            ],
            'Mr A',
        ];

        yield 'test 3: ...' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [17, 21, 20, 9, 18, 15],
                'Mr B' => [17, 21, 13, 13, 12, 24],
                'Mr C' => [10, 10, 15, 15, 25, 25],
            ],
            'Mr A',
        ];
    }
}
