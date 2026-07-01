<?php

declare(strict_types=1);

namespace Tests\App\Repository\Pronostic;

use App\Entity\Pronostic\Pronostic;
use App\Repository\Pronostic\PronosticRepository;
use Tests\App\AbstractKernelTestCase;

class PronosticRepositoryTest extends AbstractKernelTestCase
{
    private ?PronosticRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getRepository(Pronostic::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }

    public function testUnsetDisplayedExceptKeepsOnlyTheGivenOne(): void
    {
        $kept = $this->createDisplayedPronostic('Kept');
        $other1 = $this->createDisplayedPronostic('Other 1');
        $other2 = $this->createDisplayedPronostic('Other 2');
        $this->manager->flush();

        $this->repository->unsetDisplayedExcept($kept);
        $this->manager->clear();

        self::assertTrue($this->reload($kept)->displayed);
        self::assertFalse($this->reload($other1)->displayed);
        self::assertFalse($this->reload($other2)->displayed);
    }

    public function testFindDisplayedReturnsStartedPronostic(): void
    {
        $pronostic = $this->createDisplayedPronostic('Started');
        $this->manager->flush();

        self::assertSame($pronostic->getId(), $this->repository->findDisplayed()?->getId());
    }

    public function testFindDisplayedIgnoresNotStartedPronostic(): void
    {
        $pronostic = $this->createDisplayedPronostic('Not started');
        $pronostic->beginAt = new \DateTimeImmutable('+1 day');
        $this->manager->flush();

        self::assertNull($this->repository->findDisplayed());
    }

    public function testFindDisplayedIgnoresPronosticEndedForMoreThanTwentyFourHours(): void
    {
        $this->createPronostic('Expired', matchAt: '-25 hours', displayed: true);
        $this->manager->flush();

        self::assertNull($this->repository->findDisplayed());
    }

    public function testFindDisplayedReturnsFirstDisplayedPronosticByMatchAtAscending(): void
    {
        $this->createPronostic('Later', matchAt: '+2 days', displayed: true);
        $first = $this->createPronostic('First', matchAt: '+1 day', displayed: true);
        $this->manager->flush();

        self::assertSame($first->getId(), $this->repository->findDisplayed()?->getId());
    }

    public function testFindLatestReturnsMostRecentStartedByMatchAtRegardlessOfDisplayed(): void
    {
        $this->createPronostic('Old', matchAt: '-2 days', displayed: true);
        $latest = $this->createPronostic('Latest', matchAt: '+1 day', displayed: false);
        $this->createPronostic('Middle', matchAt: '-1 hour', displayed: false);
        $this->manager->flush();

        self::assertSame($latest->getId(), $this->repository->findLatest()?->getId());
    }

    public function testFindLatestIgnoresNotStartedPronostic(): void
    {
        $pronostic = $this->createPronostic('Not started', matchAt: '+3 days');
        $pronostic->beginAt = new \DateTimeImmutable('+1 day');
        $this->manager->flush();

        self::assertNull($this->repository->findLatest());
    }

    private function createDisplayedPronostic(string $title): Pronostic
    {
        return $this->createPronostic($title, displayed: true);
    }

    private function createPronostic(string $title, string $matchAt = '+1 day', bool $displayed = false): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = $title;
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->gabrielTeam1Score = 1;
        $pronostic->gabrielTeam2Score = 0;
        $pronostic->beginAt = new \DateTimeImmutable('-1 day');
        $pronostic->matchAt = new \DateTimeImmutable($matchAt);
        $pronostic->displayed = $displayed;

        $this->manager->persist($pronostic);

        return $pronostic;
    }

    private function reload(Pronostic $pronostic): Pronostic
    {
        return $this->repository->find($pronostic->getId());
    }
}
