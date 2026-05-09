<?php

declare(strict_types=1);

namespace Tests\App\Unit\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\AdherentMessageScopeInitializer;
use App\AdherentMessage\Sender\SenderInterface;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\MyTeam\MemberRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentMessageManagerTest extends TestCase
{
    public function testSendPublicationDispatchesSupportingSendersAndMarksSent(): void
    {
        $message = new AdherentMessage();

        $supportingSender = $this->createMock(SenderInterface::class);
        $supportingSender->expects(self::once())
            ->method('supports')
            ->with(self::identicalTo($message), false)
            ->willReturn(true)
        ;
        $supportingSender->expects(self::once())
            ->method('send')
            ->with(self::identicalTo($message))
        ;

        $skippingSender = $this->createMock(SenderInterface::class);
        $skippingSender->expects(self::once())
            ->method('supports')
            ->with(self::identicalTo($message), false)
            ->willReturn(false)
        ;
        $skippingSender->expects(self::never())->method('send');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $manager = $this->buildManager($em, [$supportingSender, $skippingSender]);

        $manager->sendPublication($message);

        self::assertTrue($message->isSent(), 'Publication must be marked as sent immediately.');
    }

    public function testSendPublicationFlushesAfterSendersToTriggerAlgoliaListener(): void
    {
        // The Algolia postUpdate listener fires on flush — by sending first and flushing after,
        // we guarantee that side-channel dispatches are visible BEFORE the flush triggers
        // the synchronous index update.
        $message = new AdherentMessage();
        $callOrder = [];

        $sender = $this->createMock(SenderInterface::class);
        $sender->method('supports')->willReturn(true);
        $sender->expects(self::once())
            ->method('send')
            ->with(self::identicalTo($message))
            ->willReturnCallback(function () use (&$callOrder): void {
                $callOrder[] = 'sender_send';
            })
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder): void {
                $callOrder[] = 'em_flush';
            })
        ;

        $manager = $this->buildManager($em, [$sender]);
        $manager->sendPublication($message);

        self::assertSame(['sender_send', 'em_flush'], $callOrder);
        self::assertTrue($message->isSent());
    }

    /**
     * @param iterable<SenderInterface> $senders
     */
    private function buildManager(EntityManagerInterface $em, iterable $senders): AdherentMessageManager
    {
        // AdherentMessageScopeInitializer is `final`, so we instantiate it for real with stubbed
        // dependencies. sendPublication() does not call it; this is just to satisfy the constructor.
        $scopeInitializer = new AdherentMessageScopeInitializer(
            $this->createStub(ScopeGeneratorResolver::class),
            $this->createStub(MyTeamRepository::class),
            $this->createStub(MemberRepository::class),
            $this->createStub(TranslatorInterface::class),
        );

        return new AdherentMessageManager(
            $em,
            $this->createStub(EventDispatcherInterface::class),
            $senders,
            $scopeInitializer,
        );
    }
}
