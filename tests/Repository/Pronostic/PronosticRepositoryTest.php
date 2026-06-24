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

    private function createDisplayedPronostic(string $title): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = $title;
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->gabrielTeam1Score = 1;
        $pronostic->gabrielTeam2Score = 0;
        $pronostic->beginAt = new \DateTimeImmutable('-1 day');
        $pronostic->matchAt = new \DateTimeImmutable('+1 day');
        $pronostic->displayed = true;

        $this->manager->persist($pronostic);

        return $pronostic;
    }

    private function reload(Pronostic $pronostic): Pronostic
    {
        return $this->repository->find($pronostic->getId());
    }
}
