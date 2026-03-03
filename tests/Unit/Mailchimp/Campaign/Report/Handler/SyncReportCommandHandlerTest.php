<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Report\Handler;

use App\Mailchimp\Campaign\Report\Handler\SyncReportCommandHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SyncReportCommandHandlerTest extends TestCase
{
    #[DataProvider('provideMarkSuspiciousClicksCases')]
    public function testMarkSuspiciousClicks(array $rows, array $expectedSuspicious): void
    {
        $handler = $this->createPartialMock(SyncReportCommandHandler::class, []);

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('markSuspiciousClicks');

        $result = $method->invoke($handler, $rows);

        self::assertCount(\count($expectedSuspicious), $result);

        foreach ($result as $i => $row) {
            self::assertSame(
                $expectedSuspicious[$i],
                $row['suspicious'],
                \sprintf('Row %d should have suspicious=%s', $i, $expectedSuspicious[$i] ? 'true' : 'false')
            );
        }
    }

    public static function provideMarkSuspiciousClicksCases(): iterable
    {
        // Case 1: Single click - not suspicious
        yield 'single click' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
            ],
            'expectedSuspicious' => [false],
        ];

        // Case 2: Two clicks same adherent same second - both suspicious
        yield 'two clicks same adherent same second' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link2.com'],
            ],
            'expectedSuspicious' => [true, true],
        ];

        // Case 3: Two clicks same adherent different seconds - not suspicious
        yield 'two clicks same adherent different seconds' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:01', 'target_url' => 'https://link2.com'],
            ],
            'expectedSuspicious' => [false, false],
        ];

        // Case 4: Three clicks different adherents same second - not suspicious
        yield 'three clicks different adherents same second' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 2, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 3, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
            ],
            'expectedSuspicious' => [false, false, false],
        ];

        // Case 5: Mixed - some suspicious, some not
        yield 'mixed suspicious and non-suspicious' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link2.com'],
                ['adherent_id' => 2, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:31:00', 'target_url' => 'https://link3.com'],
            ],
            'expectedSuspicious' => [true, true, false, false],
        ];

        // Case 6: Three clicks same adherent same second - all suspicious
        yield 'three clicks same adherent same second' => [
            'rows' => [
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link1.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link2.com'],
                ['adherent_id' => 1, 'app_date' => '2024-01-15 10:30:00', 'target_url' => 'https://link3.com'],
            ],
            'expectedSuspicious' => [true, true, true],
        ];

        // Case 7: Empty array
        yield 'empty array' => [
            'rows' => [],
            'expectedSuspicious' => [],
        ];
    }
}
