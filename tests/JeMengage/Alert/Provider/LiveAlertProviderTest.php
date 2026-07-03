<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Alert\Provider;

use App\Entity\Event\Event;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\LiveAlertProvider;
use App\Repository\Event\EventRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

final class LiveAlertProviderTest extends TestCase
{
    public function testPublicUserGetsLiveAlertWithoutMagicLink(): void
    {
        $event = new Event();
        $event->setName('Live national');
        $event->setSlug('live-national');
        $event->setBeginAt(new \DateTime('+1 hour'));
        $event->setFinishAt(new \DateTime('+2 hours'));

        $eventRepository = $this->createStub(EventRepository::class);
        $eventRepository->method('findWithLiveStream')->willReturn([$event]);

        $loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $loginLinkHandler->expects($this->never())->method('createLoginLink');

        $provider = new LiveAlertProvider(
            $eventRepository,
            $loginLinkHandler,
            $this->createStub(UrlGeneratorInterface::class),
        );

        $alerts = $provider->getAlerts(null);

        self::assertCount(1, $alerts);
        self::assertSame(AlertTypeEnum::LIVE_ANNOUNCE, $alerts[0]->type);
        self::assertSame('/evenements/live-national', $alerts[0]->ctaUrl);
    }
}
