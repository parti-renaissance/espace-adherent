<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\PrepareResult;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AudienceMessagePreparerTest extends TestCase
{
    public function testPrepareLockedByOtherUserReturnsConflict(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('alice@example.com');
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory());

        $result = $preparer->prepare($message, $this->createUser('bob@example.com'));

        self::assertSame(PrepareResult::STATUS_CONFLICT, $result->status);
        self::assertTrue($result->isConflict());
        self::assertSame('alice@example.com', $result->sendStatus['blocking_user']);
    }

    public function testPrepareLockedBySameUserReDispatches(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 42);
        $campaign->markAsPreparing('alice@example.com');
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PrepareCampaignAudienceMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory());

        $result = $preparer->prepare($message, $this->createUser('alice@example.com'));

        self::assertSame(PrepareResult::STATUS_PREPARING, $result->status);
        self::assertTrue($result->isPreparing());
    }

    public function testPrepareAlreadyReadyAndFilterFreshReturnsAlreadyReady(): void
    {
        $filter = new \App\Entity\AdherentMessage\AdherentMessageFilter();
        $filterReflection = new \ReflectionObject($filter);
        $updatedAtProp = $filterReflection->getProperty('updatedAt');
        $updatedAtProp->setValue($filter, new \DateTime('-1 hour'));

        $message = new AdherentMessage();
        $message->setFilter($filter);
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('alice@example.com');
        $campaign->markAsReady(100, 100, AudienceCheckEnum::Match);
        $message->addMailchimpCampaign($campaign);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory());

        $result = $preparer->prepare($message, $this->createUser('alice@example.com'));

        self::assertSame(PrepareResult::STATUS_ALREADY_READY, $result->status);
    }

    public function testPrepareFreshCampaignDispatchesAndMarksAsPreparing(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);

        $dispatched = null;
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched) {
                $dispatched = $msg;

                return new Envelope($msg);
            });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory());

        $result = $preparer->prepare($message, $this->createUser('alice@example.com'));

        self::assertTrue($result->isPreparing());
        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
        self::assertSame('alice@example.com', $campaign->getPreparationLockedBy());
        self::assertInstanceOf(PrepareCampaignAudienceMessage::class, $dispatched);
        self::assertSame('alice@example.com', $dispatched->lockedBy);
    }

    public function testPrepareNoCampaignThrowsLogicException(): void
    {
        $message = new AdherentMessage();

        $bus = $this->createStub(MessageBusInterface::class);
        $em = $this->createStub(EntityManagerInterface::class);

        $preparer = new AudienceMessagePreparer($em, $bus, new SendStatusFactory());

        $this->expectException(\LogicException::class);
        $preparer->prepare($message, $this->createUser('alice@example.com'));
    }

    public function testRequestCancellationFlipsFlagAndFlushes(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('alice@example.com');
        $message->addMailchimpCampaign($campaign);

        self::assertFalse($campaign->isCancellationRequested());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $this->createStub(MessageBusInterface::class), new SendStatusFactory());
        $preparer->requestCancellation($message);

        self::assertTrue($campaign->isCancellationRequested());
    }

    public function testRequestCancellationNoCampaignNoOp(): void
    {
        $message = new AdherentMessage();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $preparer = new AudienceMessagePreparer($em, $this->createStub(MessageBusInterface::class), new SendStatusFactory());
        $preparer->requestCancellation($message);
    }

    private function createUser(string $email): Adherent
    {
        $user = $this->createStub(Adherent::class);
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
