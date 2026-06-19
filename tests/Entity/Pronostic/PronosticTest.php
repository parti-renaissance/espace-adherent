<?php

declare(strict_types=1);

namespace App\Tests\Entity\Pronostic;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use PHPUnit\Framework\TestCase;

class PronosticTest extends TestCase
{
    public function testParticipationIsPendingBeforeResultPublication(): void
    {
        $pronostic = new Pronostic();
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);

        self::assertSame(1, $pronostic->getParticipantsCount());
        self::assertSame('En attente', $participation->getResultStatus());
    }

    public function testExactScoreWins(): void
    {
        $pronostic = new Pronostic();
        $pronostic->resultTeam1Score = 2;
        $pronostic->resultTeam2Score = 1;
        $pronostic->resultPublishedAt = new \DateTimeImmutable();

        $winner = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);
        $loser = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 1, 0);

        self::assertTrue($pronostic->isWonBy($winner));
        self::assertSame('Gagné', $winner->getResultStatus());
        self::assertFalse($pronostic->isWonBy($loser));
        self::assertSame('Perdu', $loser->getResultStatus());
    }

    public function testResultPublicationDateIsSetAutomatically(): void
    {
        $pronostic = new Pronostic();

        $pronostic->setPublishResult(true);

        self::assertTrue($pronostic->isResultPublished());
        self::assertInstanceOf(\DateTime::class, $pronostic->resultPublishedAt);

        $pronostic->setPublishResult(false);

        self::assertFalse($pronostic->isResultPublished());
        self::assertNull($pronostic->resultPublishedAt);
    }
}
