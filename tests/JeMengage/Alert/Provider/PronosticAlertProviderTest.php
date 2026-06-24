<?php

declare(strict_types=1);

namespace App\Tests\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\PronosticAlertProvider;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class PronosticAlertProviderTest extends TestCase
{
    private PronosticRepository $pronosticRepository;
    private PronosticParticipationRepository $participationRepository;
    private PronosticAlertProvider $provider;
    private Pronostic $pronostic;

    protected function setUp(): void
    {
        $this->pronosticRepository = $this->createStub(PronosticRepository::class);
        $this->participationRepository = $this->createStub(PronosticParticipationRepository::class);
        $this->provider = new PronosticAlertProvider(
            $this->pronosticRepository,
            $this->participationRepository,
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(UploaderHelperInterface::class),
        );

        $this->pronostic = new Pronostic();
        $this->pronostic->title = 'France - Sénégal';
        $this->pronostic->team1 = 'France';
        $this->pronostic->team2 = 'Sénégal';
        $this->pronostic->gabrielTeam1Score = 2;
        $this->pronostic->gabrielTeam2Score = 1;
        $this->pronostic->beginAt = new \DateTimeImmutable('-1 day');
        $this->pronostic->matchAt = new \DateTimeImmutable('+1 day');
    }

    public function testNoAlertWhenNoDisplayedPronostic(): void
    {
        $this->pronosticRepository->method('findDisplayed')->willReturn(null);

        self::assertSame([], $this->provider->getAlerts($this->createStub(Adherent::class)));
    }

    public function testNotParticipatedVersion(): void
    {
        $this->pronosticRepository->method('findDisplayed')->willReturn($this->pronostic);
        $this->participationRepository->method('findFor')->willReturn(null);

        $alerts = $this->provider->getAlerts($this->createStub(Adherent::class));

        self::assertCount(1, $alerts);
        self::assertSame(AlertTypeEnum::PRONOSTIC, $alerts[0]->type);
        self::assertSame('Je n’ai pas encore participé', $alerts[0]->label);
        self::assertSame('Participer', $alerts[0]->ctaLabel);
    }

    public function testParticipatedVersion(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->createStub(Adherent::class), 1, 0);
        $this->pronosticRepository->method('findDisplayed')->willReturn($this->pronostic);
        $this->participationRepository->method('findFor')->willReturn($participation);

        $alerts = $this->provider->getAlerts($this->createStub(Adherent::class));

        self::assertSame('J’ai participé', $alerts[0]->label);
        self::assertSame('Voir', $alerts[0]->ctaLabel);
    }

    public function testNoResultAlertForNonParticipant(): void
    {
        $this->pronostic->matchAt = new \DateTimeImmutable('-1 hour');
        $this->pronostic->resultTeam1Score = 2;
        $this->pronostic->resultTeam2Score = 1;
        $this->pronostic->resultPublishedAt = new \DateTimeImmutable('-30 minutes');

        $this->pronosticRepository->method('findDisplayed')->willReturn($this->pronostic);
        $this->participationRepository->method('findFor')->willReturn(null);

        self::assertSame([], $this->provider->getAlerts($this->createStub(Adherent::class)));
    }

    public function testResultWonVersion(): void
    {
        $this->pronostic->matchAt = new \DateTimeImmutable('-1 hour');
        $this->pronostic->resultTeam1Score = 2;
        $this->pronostic->resultTeam2Score = 1;
        $this->pronostic->resultPublishedAt = new \DateTimeImmutable('-30 minutes');
        $participation = new PronosticParticipation($this->pronostic, $this->createStub(Adherent::class), 2, 1);

        $this->pronosticRepository->method('findDisplayed')->willReturn($this->pronostic);
        $this->participationRepository->method('findFor')->willReturn($participation);

        $alerts = $this->provider->getAlerts($this->createStub(Adherent::class));

        self::assertSame('Résultat du pronostic', $alerts[0]->label);
        self::assertSame('Bravo, vous avez gagné !', $alerts[0]->description);
    }
}
