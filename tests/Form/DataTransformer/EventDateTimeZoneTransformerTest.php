<?php

namespace Tests\App\Form\DataTransformer;

use App\Entity\Adherent;
use App\Event\EventCommand;
use App\Form\DataTransformer\EventDateTimeZoneTransformer;
use PHPUnit\Framework\TestCase;

class EventDateTimeZoneTransformerTest extends TestCase
{
    /**
     * @dataProvider getDatetimeForTransformer
     */
    public function testTransform(
        string $begintAt,
        string $beginAtTransformed,
        string $finishAt,
        string $finishAtTransformed,
        string $timeZone
    ): void {
        $transformer = new EventDateTimeZoneTransformer();
        $adherent = new Adherent();
        $baseEventCommand = new EventCommand(
            $adherent,
            null,
            null,
            null,
            new \DateTime($begintAt),
            new \DateTime($finishAt),
            false,
            null,
            $timeZone
        );
        $baseEventCommand = $transformer->transform($baseEventCommand);
        $this->assertSame($beginAtTransformed, $baseEventCommand->getBeginAt()->format('Y-m-d H:i'));
        $this->assertSame($finishAtTransformed, $baseEventCommand->getFinishAt()->format('Y-m-d H:i'));
    }

    /**
     * @dataProvider getDatetimeForTransformer
     */
    public function testReverseTransform(
        string $beginAtTransformed,
        string $begintAt,
        string $finishAtTransformed,
        string $finishAt,
        string $timeZone
    ): void {
        $transformer = new EventDateTimeZoneTransformer();
        $adherent = new Adherent();
        $baseEventCommand = new EventCommand(
            $adherent,
            null,
            null,
            null,
            new \DateTime($begintAt),
            new \DateTime($finishAt),
            false,
            null,
            $timeZone
        );
        $baseEventCommand = $transformer->reverseTransform($baseEventCommand);
        $this->assertSame($beginAtTransformed, $baseEventCommand->getBeginAt()->format('Y-m-d H:i'));
        $this->assertSame($finishAtTransformed, $baseEventCommand->getFinishAt()->format('Y-m-d H:i'));
    }

    public function getDatetimeForTransformer(): \Generator
    {
        yield ['2019-01-01 01:00', '2019-01-01 01:00', '2019-01-01 03:00', '2019-01-01 03:00', 'Europe/Paris'];
        yield ['2019-07-01 01:00', '2019-07-01 01:00', '2019-07-01 03:00', '2019-07-01 03:00', 'Europe/Paris']; // DST
        yield ['2019-01-01 01:00', '2019-01-01 01:00', '2019-01-01 03:00', '2019-01-01 03:00', 'Europe/Zurich'];
        yield ['2019-01-01 01:00', '2019-01-01 00:00', '2019-01-01 03:00', '2019-01-01 02:00', 'Europe/London'];
        yield ['2019-01-01 01:00', '2018-12-31 19:00', '2019-01-01 03:00', '2018-12-31 21:00', 'America/New_York'];
        yield ['2019-01-01 01:00', '2018-12-31 16:00', '2019-01-01 03:00', '2018-12-31 18:00', 'America/Los_Angeles'];
        yield ['2019-01-01 01:00', '2019-01-01 08:00', '2019-01-01 03:00', '2019-01-01 10:00', 'Asia/Singapore'];
    }
}
