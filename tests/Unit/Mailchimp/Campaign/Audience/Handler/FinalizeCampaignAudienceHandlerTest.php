<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\AudienceCheckCalculator;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\Handler\FinalizeCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class FinalizeCampaignAudienceHandlerTest extends TestCase
{
    public function testCampaignNotFoundReturnsEarly(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn(null);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(AdherentMessageTargetedRepository::class);
        $repo->expects(self::never())->method('existsPending');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getSegment');

        $handler = $this->buildHandler($em, $repo, $driver);
        $handler(new FinalizeCampaignAudienceMessage(99));
    }

    public function testAlreadyReadyIsIdempotentNoOp(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsReady(AudienceCheckEnum::Match);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(AdherentMessageTargetedRepository::class);
        $repo->expects(self::never())->method('existsPending');
        $repo->expects(self::never())->method('aggregateStatusCounts');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getSegment');

        $handler = $this->buildHandler($em, $repo, $driver);
        $handler(new FinalizeCampaignAudienceMessage(7));
    }

    public function testCancellationRequestedSkips(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing('alice@example.com');
        $campaign->requestCancellation();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(AdherentMessageTargetedRepository::class);
        $repo->expects(self::never())->method('existsPending');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getSegment');

        $handler = $this->buildHandler($em, $repo, $driver);
        $handler(new FinalizeCampaignAudienceMessage(7));
    }

    public function testStillPendingDelaysFinalization(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing('alice@example.com');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(AdherentMessageTargetedRepository::class);
        $repo->expects(self::once())->method('existsPending')->with(100)->willReturn(true);
        $repo->expects(self::never())->method('aggregateStatusCounts');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('getSegment');

        $handler = $this->buildHandler($em, $repo, $driver);
        $handler(new FinalizeCampaignAudienceMessage(7));
    }

    public function testHappyPathAggregatesCountsAndMarksReady(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing('alice@example.com');
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->expectedCount = 1_000;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::once())->method('flush');

        $repo = $this->createMock(AdherentMessageTargetedRepository::class);
        $repo->method('existsPending')->willReturn(false);
        $repo->expects(self::once())
            ->method('aggregateStatusCounts')
            ->with(100)
            ->willReturn([
                TargetedProcessingStatusEnum::Added->value => 950,
                TargetedProcessingStatusEnum::Refused->value => 30,
                TargetedProcessingStatusEnum::Errored->value => 20,
            ])
        ;

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('getSegment')
            ->with(555, 'main-list')
            ->willReturn(['member_count' => 950])
        ;

        $handler = $this->buildHandler($em, $repo, $driver);
        $handler(new FinalizeCampaignAudienceMessage(7));

        self::assertSame(950, $segment->preparedCount);
        self::assertSame(30, $segment->refusedCount);
        self::assertSame(20, $segment->erroredCount);
        self::assertNotNull($segment->builtAt);
        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
    }

    private function buildHandler(
        EntityManagerInterface $em,
        AdherentMessageTargetedRepository $repo,
        Driver $driver,
    ): FinalizeCampaignAudienceHandler {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn('main-list');

        return new FinalizeCampaignAudienceHandler($em, $repo, $driver, $mapping, new AudienceCheckCalculator());
    }

    private function buildCampaign(AdherentMessage $message, int $segmentId): MailchimpCampaign
    {
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);
        $campaign->setStaticSegmentId($segmentId);
        $segment = new MailchimpStaticSegment($campaign);
        $this->setEntityId($segment, 4242);
        $segment->mailchimpSegmentId = $segmentId;
        $campaign->setMailchimpStaticSegment($segment);

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
