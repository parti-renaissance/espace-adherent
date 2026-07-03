<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\AppAlert;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\AppAlertProvider;
use App\Repository\AppAlertRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

final class AppAlertProviderTest extends TestCase
{
    public function testPublicUserGetsOnlyPublicAlertTypedAlerts(): void
    {
        $publicAlert = $this->alert('Alerte publique', AlertTypeEnum::ALERT, true);
        $privateAlert = $this->alert('Alerte privée', AlertTypeEnum::ALERT, false);
        $meetingAlert = $this->alert('Meeting', AlertTypeEnum::MEETING, true);
        $electionAlert = $this->alert('Consultation', AlertTypeEnum::ELECTION, true);

        $repository = $this->createStub(AppAlertRepository::class);
        $repository->method('findAllActive')->willReturn([$publicAlert, $privateAlert, $meetingAlert, $electionAlert]);

        $loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $loginLinkHandler->expects($this->never())->method('createLoginLink');

        $alerts = $this->createProvider($repository, $loginLinkHandler)->getAlerts(null);

        self::assertSame(['Alerte publique'], array_map(static fn ($alert): string => $alert->title, $alerts));
        self::assertSame('/public', $alerts[0]->ctaUrl);
    }

    public function testAdherentGetsAllActiveGenericAlerts(): void
    {
        $publicAlert = $this->alert('Alerte publique', AlertTypeEnum::ALERT, true);
        $privateAlert = $this->alert('Alerte privée', AlertTypeEnum::ALERT, false);

        $repository = $this->createStub(AppAlertRepository::class);
        $repository->method('findAllActive')->willReturn([$publicAlert, $privateAlert]);

        $alerts = $this->createProvider($repository)->getAlerts($this->createStub(Adherent::class));

        self::assertSame(['Alerte publique', 'Alerte privée'], array_map(static fn ($alert): string => $alert->title, $alerts));
    }

    private function createProvider(
        AppAlertRepository $repository,
        ?LoginLinkHandlerInterface $loginLinkHandler = null,
    ): AppAlertProvider {
        return new AppAlertProvider(
            $repository,
            $loginLinkHandler ?? $this->createStub(LoginLinkHandlerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(UploaderHelperInterface::class),
        );
    }

    private function alert(string $title, AlertTypeEnum $type, bool $isPublic): AppAlert
    {
        $alert = new AppAlert();
        $alert->type = $type;
        $alert->label = $title;
        $alert->title = $title;
        $alert->description = 'Description';
        $alert->ctaLabel = 'Voir';
        $alert->ctaUrl = '/public';
        $alert->beginAt = new \DateTimeImmutable('-1 day');
        $alert->endAt = new \DateTimeImmutable('+1 day');
        $alert->isPublic = $isPublic;

        return $alert;
    }
}
