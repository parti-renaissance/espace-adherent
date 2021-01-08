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
    public function testMajorityJudgmentProcessReturnGoodWinner(
        array $candidates,
        array $votingProfiles,
        ?string $expectedWinner
    ): void {
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

        if ($expectedWinner) {
            self::assertSame($expectedWinner, $election->getWinner()->getIdentifier());
        } else {
            self::assertNull($election->getWinner());
        }
    }

    public function getElectionData(): \Generator
    {
        yield 'simple test 1: vote with 2 candidates and 2 votes' => [
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

        yield 'simple test 2: vote with 2 candidates and 2 two votes' => [
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

        yield 'simple test 3: vote with 3 candidates and 100 votes' => [
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

        yield 'equals test 1: simple case when biggest proponent wins' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [15, 18, 9, 20, 21, 17],
                'Mr B' => [24, 12, 13, 13, 21, 17],
                'Mr C' => [25, 20, 5, 32, 8, 10],
            ],
            'Mr C',
        ];

        yield 'equals test 2: 2 biggest opponents lose and the remainder wins' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [15, 18, 9, 20, 21, 17],
                'Mr B' => [23, 5, 10, 13, 34, 15],
                'Mr C' => [15, 1, 5, 30, 21, 28],
            ],
            'Mr A',
        ];

        yield 'equals test 3: 1 biggest opponent loses and the remainder wins' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [15, 18, 9, 20, 21, 17],
                'Mr B' => [23, 5, 10, 13, 34, 15],
                'Mr C' => [21, 12, 5, 30, 13, 19],
            ],
            'Mr A',
        ];

        yield 'equals test 4: 1 biggest opponent loses, then another remainder biggest opponent loses too, and remainder candidate wins' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [15, 13, 9, 25, 21, 17],
                'Mr B' => [23, 5, 10, 13, 34, 15],
                'Mr C' => [21, 11, 5, 30, 14, 19],
            ],
            'Mr C',
        ];

        yield 'equals test 5: 1 biggest opponent loses, then equals between opponent & proponent of 1 candidate => he loses too' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [12, 12, 11, 30, 14, 21],
                'Mr B' => [23, 5, 10, 13, 34, 15],
                'Mr C' => [17, 12, 9, 24, 21, 17],
            ],
            'Mr A',
        ];

        yield 'equals test 6: 1 biggest opponent loses, then equals between 2 proponents' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [15, 13, 9, 27, 21, 15],
                'Mr B' => [23, 5, 10, 13, 34, 15],
                'Mr C' => [21, 11, 5, 30, 14, 19],
            ],
            'Mr C',
        ];

        yield 'equals test 7: double equals with proponents' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [12, 13, 9, 38, 17, 11],
                'Mr B' => [21, 5, 12, 13, 34, 15],
                'Mr C' => [17, 11, 9, 35, 14, 14],
            ],
            'Mr C',
        ];

        yield 'equals test 8: double equals, first between proponents and after between opponent & proponent of the same candidate' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [21, 5, 12, 13, 34, 15],
                'Mr B' => [0, 17, 32, 34, 0, 17],
                'Mr C' => [0, 0, 49, 34, 17, 0],
            ],
            'Mr C',
        ];

        yield 'test 9: no winner' => [
            [
                'Mr A',
                'Mr B',
                'Mr C',
            ],
            [
                'Mr A' => [1, 1, 1, 1, 1, 1],
                'Mr B' => [1, 1, 1, 1, 1, 1],
                'Mr C' => [1, 1, 1, 1, 1, 1],
            ],
            null,
        ];
    }
}
