<?php

declare(strict_types=1);

namespace App\Tests\Pronostic;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\Pronostic\PronosticViewFactory;
use PHPUnit\Framework\TestCase;

class PronosticViewFactoryTest extends TestCase
{
    private PronosticViewFactory $factory;
    private Pronostic $pronostic;
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->factory = new PronosticViewFactory();
        $this->now = new \DateTimeImmutable('2026-06-19 12:00:00');
        $this->pronostic = new Pronostic();
        $this->pronostic->title = 'France - Sénégal';
        $this->pronostic->team1 = 'France';
        $this->pronostic->team2 = 'Sénégal';
        $this->pronostic->gabrielTeam1Score = 2;
        $this->pronostic->gabrielTeam2Score = 1;
        $this->pronostic->beginAt = $this->now->modify('-1 day');
        $this->pronostic->matchAt = $this->now->modify('+1 day');
    }

    public function testNotParticipatedViewDoesNotExposeResult(): void
    {
        $view = $this->factory->create($this->pronostic, null, $this->now);

        self::assertSame('not_participated', $view['status']);
        self::assertNull($view['participation']);
        self::assertArrayNotHasKey('result', $view);
        self::assertArrayNotHasKey('won', $view);
    }

    public function testScheduledView(): void
    {
        $this->pronostic->beginAt = $this->now->modify('+1 hour');

        $view = $this->factory->create($this->pronostic, null, $this->now);

        self::assertSame('scheduled', $view['status']);
    }

    public function testParticipatedView(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->createStub(Adherent::class), 1, 0);

        $view = $this->factory->create($this->pronostic, $participation, $this->now);

        self::assertSame('participated', $view['status']);
        self::assertSame(['team_1_score' => 1, 'team_2_score' => 0], $view['participation']);
    }

    public function testPublishedResultIsHiddenWithoutParticipation(): void
    {
        $this->pronostic->resultTeam1Score = 2;
        $this->pronostic->resultTeam2Score = 1;
        $this->pronostic->resultPublishedAt = $this->now;
        $this->pronostic->matchAt = $this->now->modify('-1 hour');

        $view = $this->factory->create($this->pronostic, null, $this->now);

        self::assertSame('closed', $view['status']);
        self::assertArrayNotHasKey('result', $view);
        self::assertArrayNotHasKey('won', $view);
    }

    public function testPublishedResultExposesOutcome(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->createStub(Adherent::class), 2, 1);
        $this->pronostic->resultTeam1Score = 2;
        $this->pronostic->resultTeam2Score = 1;
        $this->pronostic->resultPublishedAt = $this->now;

        $view = $this->factory->create($this->pronostic, $participation, $this->now);

        self::assertSame('result_available', $view['status']);
        self::assertSame(['team_1_score' => 2, 'team_2_score' => 1], $view['result']);
        self::assertTrue($view['won']);
    }
}
