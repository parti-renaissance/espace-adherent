<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Alert;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\JeMengage\Alert\AlertProvider;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\AlertProviderInterface;
use PHPUnit\Framework\TestCase;

final class AlertProviderTest extends TestCase
{
    public function testGetAlertsSortsByEndDateAscending(): void
    {
        $soon = $this->alert('Soon', '2026-06-18 10:00:00');
        $middle = $this->alert('Middle', '2026-06-20 10:00:00');
        $late = $this->alert('Late', '2026-06-25 10:00:00');

        $provider = new AlertProvider([
            $this->provider([$late, $soon]),
            $this->provider([$middle]),
        ]);

        self::assertSame(
            ['Soon', 'Middle', 'Late'],
            array_map(static fn (Alert $alert): string => $alert->title, $provider->getAlerts($this->createStub(Adherent::class)))
        );
    }

    private function alert(string $title, string $endDate): Alert
    {
        $alert = new Alert(
            AlertTypeEnum::ALERT,
            'Alerte',
            $title,
            'Description',
        );
        $alert->date = new \DateTimeImmutable($endDate);

        return $alert;
    }

    /**
     * @param Alert[] $alerts
     */
    private function provider(array $alerts): AlertProviderInterface
    {
        return new class($alerts) implements AlertProviderInterface {
            /**
             * @param Alert[] $alerts
             */
            public function __construct(private readonly array $alerts)
            {
            }

            public function getAlerts(?Adherent $adherent): array
            {
                return $this->alerts;
            }
        };
    }
}
