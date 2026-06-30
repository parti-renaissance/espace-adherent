<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\PrepareResult;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AudienceMessagePreparerTest extends TestCase
{
    public function testPrepareLockedByOtherUserReturnsConflict(): void
    {
        $alice = $this->createUser(1, 'alice@example.com');
        $bob = $this->createUser(2, 'bob@example.com');

        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing($alice);
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $this->createStub(StaticSegmentInitializer::class));

        $result = $preparer->prepare($message, $bob);

        self::assertSame(PrepareResult::STATUS_CONFLICT, $result->status);
        self::assertTrue($result->isConflict());
        self::assertFalse($campaign->isPendingSend(), 'pendingSend must NOT be set on conflict — bug fix from /05-07-send-segment-prep.');
    }

    public function testPrepareLockedBySameUserReDispatches(): void
    {
        $alice = $this->createUser(1, 'alice@example.com');

        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 42);
        $campaign->markAsPreparing($alice);
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PrepareCampaignAudienceMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $this->createStub(StaticSegmentInitializer::class));

        $result = $preparer->prepare($message, $alice);

        self::assertSame(PrepareResult::STATUS_PREPARING, $result->status);
        self::assertTrue($result->isPreparing());
    }

    public function testPrepareMarksPreparingAndPendingSendAtomically(): void
    {
        $alice = $this->createUser(1, 'alice@example.com');

        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);

        $dispatched = null;
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PrepareCampaignAudienceMessage::class))
            ->willReturnCallback(function (object $msg) use (&$dispatched) {
                $dispatched = $msg;

                return new Envelope($msg);
            });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $this->createStub(StaticSegmentInitializer::class));

        $result = $preparer->prepare($message, $alice);

        self::assertTrue($result->isPreparing());
        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
        self::assertSame($alice, $campaign->getPreparationLockedBy());
        self::assertTrue($campaign->isPendingSend());
        self::assertInstanceOf(PrepareCampaignAudienceMessage::class, $dispatched);
        self::assertSame(1, $dispatched->lockedById);
    }

    public function testPrepareEnsuresLocalSegmentForResolvedCampaign(): void
    {
        $alice = $this->createUser(1, 'alice@example.com');

        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);

        $initializer = $this->createMock(StaticSegmentInitializer::class);
        $initializer
            ->expects(self::once())
            ->method('ensureLocalSegment')
            ->with(self::identicalTo($campaign));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PrepareCampaignAudienceMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $initializer);

        $result = $preparer->prepare($message, $alice);

        self::assertTrue($result->isPreparing());
    }

    public function testPrepareDispatchFailureLogsErrorAndRethrows(): void
    {
        $alice = $this->createUser(1, 'alice@example.com');

        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PrepareCampaignAudienceMessage::class))
            ->willThrowException(new \RuntimeException('broker down'));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[AudienceMessagePreparer] PrepareCampaignAudienceMessage dispatch failed',
                self::callback(function (array $context): bool {
                    return 7 === $context['campaign_id']
                        && $context['exception'] instanceof \RuntimeException
                        && 'broker down' === $context['exception']->getMessage();
                }),
            );

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $this->createStub(StaticSegmentInitializer::class), $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('broker down');

        $preparer->prepare($message, $alice);
    }

    public function testPrepareNoCampaignThrowsLogicException(): void
    {
        $message = new AdherentMessage();

        $bus = $this->createStub(MessageBusInterface::class);
        $em = $this->createStub(EntityManagerInterface::class);

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory(), $this->createStub(StaticSegmentInitializer::class));

        $this->expectException(\LogicException::class);
        $preparer->prepare($message, $this->createUser(1, 'alice@example.com'));
    }

    private function createUser(int $id, string $email): Adherent
    {
        $user = $this->createStub(Adherent::class);
        $user->method('getId')->willReturn($id);
        $user->method('getEmailAddress')->willReturn($email);

        return $user;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
