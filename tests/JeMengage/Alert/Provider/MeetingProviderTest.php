<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Alert\Provider;

use App\Entity\NationalEvent\NationalEvent;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\MeetingProvider;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

final class MeetingProviderTest extends TestCase
{
    public function testPublicUserGetsMeetingAlertWithoutMagicLink(): void
    {
        $event = new NationalEvent();
        $event->setName('Campus');
        $event->updateSlug('campus');
        $event->startDate = new \DateTime('+1 day');
        $event->endDate = new \DateTime('+2 days');
        $event->alertTitle = 'Venez nombreux !';
        $event->alertDescription = '';

        $eventRepository = $this->createStub(NationalEventRepository::class);
        $eventRepository->method('findOneActiveForAlert')->willReturn([$event]);

        $eventInscriptionRepository = $this->createMock(EventInscriptionRepository::class);
        $eventInscriptionRepository->expects($this->never())->method('findAllForAdherentAndEvent');

        $loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $loginLinkHandler->expects($this->never())->method('createLoginLink');

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn(null);

        $provider = new MeetingProvider(
            $eventRepository,
            $eventInscriptionRepository,
            $loginLinkHandler,
            $this->urlGenerator(),
            $security,
            $this->createStub(UploaderHelperInterface::class),
        );

        $alerts = $provider->getAlerts(null);

        self::assertCount(1, $alerts);
        self::assertSame(AlertTypeEnum::MEETING, $alerts[0]->type);
        self::assertSame('Je réserve ma place', $alerts[0]->ctaLabel);
        self::assertSame('http://test.renaissance.code/grand-rassemblement/campus?utm_source=app&utm_campaign=alerte', $alerts[0]->ctaUrl);
        self::assertSame('http://test.renaissance.code/grand-rassemblement/campus?utm_source=app&utm_campaign=alerte', $alerts[0]->shareUrl);
    }

    private function urlGenerator(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(static function (string $route, array $parameters = []): string {
                self::assertSame('app_national_event_by_slug', $route);

                return \sprintf(
                    'http://test.renaissance.code/grand-rassemblement/%s?utm_source=%s&utm_campaign=%s',
                    $parameters['slug'],
                    $parameters['utm_source'],
                    $parameters['utm_campaign'],
                );
            })
        ;

        return $urlGenerator;
    }
}
