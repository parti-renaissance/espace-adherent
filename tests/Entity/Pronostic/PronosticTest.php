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
        $pronostic = $this->createPublishedPronostic(2, 1, 3, 1);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);

        self::assertTrue($pronostic->isWonBy($participation));
        self::assertSame('won', $participation->getResultStatusCode());
        self::assertSame('Gagné', $participation->getResultStatus());
    }

    public function testCorrectOutcomeWinsAgainstWrongOutcome(): void
    {
        $pronostic = $this->createPublishedPronostic(1, 1, 2, 1);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 2);

        self::assertSame('won', $participation->getResultStatusCode());
    }

    public function testWrongOutcomeLosesAgainstCorrectOutcome(): void
    {
        $pronostic = $this->createPublishedPronostic(1, 1, 2, 2);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);

        self::assertFalse($pronostic->isWonBy($participation));
        self::assertSame('lost', $participation->getResultStatusCode());
        self::assertSame('Perdu', $participation->getResultStatus());
    }

    public function testBothExactScoresEndInDraw(): void
    {
        $pronostic = $this->createPublishedPronostic(2, 1, 2, 1);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);

        self::assertFalse($pronostic->isWonBy($participation));
        self::assertSame('draw', $participation->getResultStatusCode());
        self::assertSame('Match nul', $participation->getResultStatus());
    }

    public function testPrecisionWinsWhenNoOneHasCorrectOutcome(): void
    {
        $pronostic = $this->createPublishedPronostic(2, 0, 0, 1);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 3, 1);

        self::assertSame('won', $participation->getResultStatusCode());
    }

    public function testSamePrecisionEndsInDraw(): void
    {
        $pronostic = $this->createPublishedPronostic(2, 0, 3, 0);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 1);

        self::assertSame('draw', $participation->getResultStatusCode());
    }

    public function testDrawResultCanBePredictedAsCorrectOutcome(): void
    {
        $pronostic = $this->createPublishedPronostic(1, 1, 2, 1);
        $participation = new PronosticParticipation($pronostic, $this->createStub(Adherent::class), 2, 2);

        self::assertSame('won', $participation->getResultStatusCode());
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

    private function createPublishedPronostic(int $resultTeam1Score, int $resultTeam2Score, int $gabrielTeam1Score, int $gabrielTeam2Score): Pronostic
    {
        $pronostic = $this->createValidPronostic();
        $pronostic->resultTeam1Score = $resultTeam1Score;
        $pronostic->resultTeam2Score = $resultTeam2Score;
        $pronostic->resultPublishedAt = new \DateTimeImmutable();
        $pronostic->gabrielTeam1Score = $gabrielTeam1Score;
        $pronostic->gabrielTeam2Score = $gabrielTeam2Score;

        return $pronostic;
    }
}
