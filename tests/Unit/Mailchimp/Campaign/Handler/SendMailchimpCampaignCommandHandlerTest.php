<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Handler\SendMailchimpCampaignCommandHandler;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SendMailchimpCampaignCommandHandlerTest extends TestCase
{
    public function testInvokeWithMissingCampaignReturnsEarly(): void
    {
        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(99)->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('refresh');
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getCampaignSavedSegmentId');

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('sendMailchimpCampaign');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(
                '[SendMailchimpCampaign] MailchimpCampaign not found',
                self::callback(fn (array $ctx): bool => 99 === $ctx['campaign_id']),
            )
        ;

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus, $logger);
        $handler(new SendMailchimpCampaignCommand(99));
    }

    public function testInvokeWithCampaignAlreadySentSkipsAndReturns(): void
    {
        $campaign = $this->buildCampaign(externalId: 'mc-abc', staticSegmentId: 555);
        $campaign->status = MailchimpStatusEnum::Sent;

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getCampaignSavedSegmentId');

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('sendMailchimpCampaign');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus);
        $handler(new SendMailchimpCampaignCommand(7));
    }

    public function testInvokeWithCampaignAlreadySendingSkipsAndReturns(): void
    {
        $campaign = $this->buildCampaign(externalId: 'mc-abc', staticSegmentId: 555);
        $campaign->status = MailchimpStatusEnum::Sending;

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getCampaignSavedSegmentId');

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('sendMailchimpCampaign');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus);
        $handler(new SendMailchimpCampaignCommand(7));
    }

    public function testInvokeWithMissingExternalIdLogsErrorAndReturns(): void
    {
        $campaign = $this->buildCampaign(externalId: null, staticSegmentId: 555);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getCampaignSavedSegmentId');

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('sendMailchimpCampaign');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[SendMailchimpCampaign] Missing external id — send aborted',
                self::callback(fn (array $ctx): bool => 7 === $ctx['campaign_id']),
            )
        ;

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus, $logger);
        $handler(new SendMailchimpCampaignCommand(7));
    }

    public function testInvokeWithSegmentMismatchLogsErrorMarksErrorAndThrows(): void
    {
        $campaign = $this->buildCampaign(externalId: 'mc-abc', staticSegmentId: 555);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        $em->expects(self::once())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with('mc-abc')
            ->willReturn(999)
        ;

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('sendMailchimpCampaign');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[SendMailchimpCampaign] Segment mismatch — send aborted',
                self::callback(function (array $ctx): bool {
                    return 7 === $ctx['campaign_id']
                        && 'mc-abc' === $ctx['external_id']
                        && 555 === $ctx['local_segment_id']
                        && 999 === $ctx['remote_segment_id'];
                }),
            )
        ;

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus, $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Segment mismatch: local=555 remote=999/');

        try {
            $handler(new SendMailchimpCampaignCommand(7));
        } finally {
            self::assertSame(MailchimpStatusEnum::Error, $campaign->status);
            self::assertNotNull($campaign->getDetail());
            self::assertStringContainsString('local=555', $campaign->getDetail());
            self::assertStringContainsString('remote=999', $campaign->getDetail());
        }
    }

    public function testInvokeWithMatchingSegmentDelegatesToManager(): void
    {
        $campaign = $this->buildCampaign(externalId: 'mc-abc', staticSegmentId: 555);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        // Manager::sendMailchimpCampaign now owns its own flushes; the handler does not flush on the happy path.
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with('mc-abc')
            ->willReturn(555)
        ;

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())
            ->method('sendMailchimpCampaign')
            ->with(self::identicalTo($campaign))
            ->willReturn(true)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus);
        $handler(new SendMailchimpCampaignCommand(7));
    }

    public function testInvokeDispatchesRetryWhenManagerReportsFailure(): void
    {
        $campaign = $this->buildCampaign(externalId: 'mc-abc', staticSegmentId: 555);

        $repository = $this->createMock(MailchimpCampaignRepository::class);
        $repository->expects(self::once())->method('find')->with(7)->willReturn($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('refresh')->with(self::identicalTo($campaign));
        $em->expects(self::never())->method('flush');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('getCampaignSavedSegmentId')
            ->with('mc-abc')
            ->willReturn(555)
        ;

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())
            ->method('sendMailchimpCampaign')
            ->with(self::identicalTo($campaign))
            ->willReturn(false)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (RetrySendMailchimpCampaignCommand $cmd): bool {
                    return 7 === $cmd->campaignId && 0 === $cmd->countRetry;
                }),
                self::callback(function (array $stamps): bool {
                    return 1 === \count($stamps)
                        && $stamps[0] instanceof DelayStamp
                        && 30_000 === $stamps[0]->getDelay();
                }),
            )
            ->willReturnCallback(fn (object $cmd): Envelope => new Envelope($cmd))
        ;

        $handler = new SendMailchimpCampaignCommandHandler($repository, $em, $driver, $manager, $bus);
        $handler(new SendMailchimpCampaignCommand(7));
    }

    private function buildCampaign(?string $externalId, ?int $staticSegmentId): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        if (null !== $externalId) {
            $campaign->setExternalId($externalId);
        }
        if (null !== $staticSegmentId) {
            $campaign->setStaticSegmentId($staticSegmentId);
        }
        $message->addMailchimpCampaign($campaign);

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
