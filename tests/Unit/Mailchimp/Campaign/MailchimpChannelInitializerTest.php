<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\MailchimpChannelInitializer;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use App\Mailchimp\Manager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class MailchimpChannelInitializerTest extends TestCase
{
    public function testEnsureRemoteChannelCreatesSegmentBeforeEditingCampaign(): void
    {
        $campaign = $this->buildCampaign(staticSegmentId: null);

        $staticSegmentInitializer = $this->createMock(StaticSegmentInitializer::class);
        $staticSegmentInitializer->expects(self::once())->method('ensureLocalSegment')->with($campaign);

        $objectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $objectIdMapping->expects(self::once())->method('getMainListId')->willReturn('list-123');

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::once())
            ->method('create')
            ->with(self::stringStartsWith('PROD_'), [], 'list-123')
            ->willReturn(999);

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())
            ->method('editCampaign')
            ->with(self::callback(function (MailchimpCampaign $campaign): bool {
                // Order proof: the segment id is already set when the remote campaign is built,
                // so its request carries the saved_segment_id.
                return 999 === $campaign->getStaticSegmentId();
            }))
            ->willReturn(true);
        $manager->expects(self::once())->method('editCampaignContent')->with($campaign)->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $this->buildInitializer($staticSegmentInitializer, $staticSegmentService, $objectIdMapping, $manager, $em)
            ->ensureRemoteChannel($campaign);

        self::assertSame(999, $campaign->getStaticSegmentId());
        self::assertSame(999, $campaign->getMailchimpStaticSegment()->mailchimpSegmentId);
    }

    public function testEnsureRemoteChannelIsIdempotentWhenSegmentAlreadyCreated(): void
    {
        $campaign = $this->buildCampaign(staticSegmentId: 555);

        $staticSegmentInitializer = $this->createMock(StaticSegmentInitializer::class);
        $staticSegmentInitializer->expects(self::once())->method('ensureLocalSegment')->with($campaign);

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::never())->method('create');

        $objectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $objectIdMapping->expects(self::never())->method('getMainListId');

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())->method('editCampaign')->with($campaign)->willReturn(true);
        $manager->expects(self::once())->method('editCampaignContent')->with($campaign)->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $this->buildInitializer($staticSegmentInitializer, $staticSegmentService, $objectIdMapping, $manager, $em)
            ->ensureRemoteChannel($campaign);

        self::assertSame(555, $campaign->getStaticSegmentId());
    }

    public function testEnsureRemoteChannelThrowsAndSkipsCampaignWhenSegmentCreationFails(): void
    {
        $campaign = $this->buildCampaign(staticSegmentId: null);

        $staticSegmentInitializer = $this->createMock(StaticSegmentInitializer::class);
        $staticSegmentInitializer->expects(self::once())->method('ensureLocalSegment')->with($campaign);

        $objectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $objectIdMapping->expects(self::once())->method('getMainListId')->willReturn('list-123');

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::once())
            ->method('create')
            ->with(self::stringStartsWith('PROD_'), [], 'list-123')
            ->willReturn(null);

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('editCampaign');
        $manager->expects(self::never())->method('editCampaignContent');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $this->expectException(\RuntimeException::class);

        $this->buildInitializer($staticSegmentInitializer, $staticSegmentService, $objectIdMapping, $manager, $em)
            ->ensureRemoteChannel($campaign);
    }

    private function buildInitializer(
        StaticSegmentInitializer $staticSegmentInitializer,
        MailchimpStaticSegmentServiceInterface $staticSegmentService,
        MailchimpObjectIdMapping $objectIdMapping,
        Manager $manager,
        EntityManagerInterface $em,
    ): MailchimpChannelInitializer {
        return new MailchimpChannelInitializer($staticSegmentInitializer, $staticSegmentService, $objectIdMapping, $manager, $em);
    }

    private function buildCampaign(?int $staticSegmentId): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);

        $segment = new MailchimpStaticSegment($campaign);
        $segment->name = 'PROD_test-uuid';
        $campaign->setMailchimpStaticSegment($segment);

        if (null !== $staticSegmentId) {
            $campaign->setStaticSegmentId($staticSegmentId);
            $segment->mailchimpSegmentId = $staticSegmentId;
        }

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
