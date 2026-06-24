<?php

declare(strict_types=1);

namespace App\Tests\Entity\Pronostic;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\Entity\UploadableFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

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
        self::assertInstanceOf(\DateTimeImmutable::class, $pronostic->resultPublishedAt);

        $pronostic->setPublishResult(false);

        self::assertFalse($pronostic->isResultPublished());
        self::assertNull($pronostic->resultPublishedAt);
    }

    public function testImageIsRequiredOnAdminCreation(): void
    {
        $pronostic = $this->createValidPronostic();

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($pronostic, groups: ['Admin_creation']);

        self::assertCount(1, $violations);
        self::assertSame('image', $violations[0]->getPropertyPath());
    }

    public function testImageIsNotRequiredOnAdminModification(): void
    {
        $pronostic = $this->createValidPronostic();

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($pronostic, groups: ['Admin_modification']);

        self::assertCount(0, $violations);
    }

    public function testImagePassesAdminCreationValidationWhenPresent(): void
    {
        $pronostic = $this->createValidPronostic();
        $pronostic->image = new UploadableFile();

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($pronostic, groups: ['Admin_creation']);

        self::assertCount(0, $violations);
    }

    private function createValidPronostic(): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = 'France - Sénégal';
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->gabrielTeam1Score = 2;
        $pronostic->gabrielTeam2Score = 1;
        $pronostic->beginAt = new \DateTimeImmutable('-1 day');
        $pronostic->matchAt = new \DateTimeImmutable('+1 day');

        return $pronostic;
    }
}
