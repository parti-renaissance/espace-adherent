<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Listener;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Listener\InitiateStaticSegmentSubscriber;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Events;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InitiateStaticSegmentSubscriberTest extends TestCase
{
    private const MAIN_LIST_ID = 'list-main';
    private const ELECTED_LIST_ID = 'list-elected';

    public function testListensToCampaignFiltersPreBuild(): void
    {
        self::assertSame(
            [Events::CAMPAIGN_FILTERS_PRE_BUILD => 'onCampaignFiltersPreBuild'],
            InitiateStaticSegmentSubscriber::getSubscribedEvents()
        );
    }

    public function testCreatesEmptySegmentAndBindsItToCampaignWhenStaticSegmentIdIsNull(): void
    {
        $uuid = Uuid::uuid4();
        $campaign = $this->createCampaign($uuid);

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())
            ->method('create')
            ->with(\sprintf('PROD_%s', $uuid->toString()), [], self::MAIN_LIST_ID)
            ->willReturn(4242);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $subscriber = new InitiateStaticSegmentSubscriber(
            $segmentService,
            $this->mappingStub(),
            $em,
        );

        $subscriber->onCampaignFiltersPreBuild(new CampaignEvent($campaign));

        self::assertSame(4242, $campaign->getStaticSegmentId());
        self::assertSame(\sprintf('PROD_%s', $uuid->toString()), $campaign->getMailchimpStaticSegment()->name);
    }

    public function testNoOpWhenStaticSegmentIdAlreadySet(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setStaticSegmentId(99);

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::never())->method('create');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $subscriber = new InitiateStaticSegmentSubscriber($segmentService, $this->mappingStub(), $em);
        $subscriber->onCampaignFiltersPreBuild(new CampaignEvent($campaign));

        self::assertSame(99, $campaign->getStaticSegmentId());
    }

    public function testUsesMailchimpListTypeWhenSet(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setMailchimpListType('elected_representative');

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())
            ->method('create')
            ->with(self::stringStartsWith('PROD_'), [], self::ELECTED_LIST_ID)
            ->willReturn(7);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $subscriber = new InitiateStaticSegmentSubscriber($segmentService, $this->mappingStub(), $em);
        $subscriber->onCampaignFiltersPreBuild(new CampaignEvent($campaign));

        self::assertSame(7, $campaign->getStaticSegmentId());
    }

    public function testDoesNotPersistWhenSegmentCreationReturnsNull(): void
    {
        $campaign = $this->createCampaign();

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->method('create')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $subscriber = new InitiateStaticSegmentSubscriber($segmentService, $this->mappingStub(), $em);
        $subscriber->onCampaignFiltersPreBuild(new CampaignEvent($campaign));

        self::assertNull($campaign->getStaticSegmentId());
        self::assertNull($campaign->getMailchimpStaticSegment());
    }

    private function createCampaign(?UuidInterface $uuid = null): MailchimpCampaign
    {
        $message = $this->createStub(AdherentMessageInterface::class);
        $message->method('getUuid')->willReturn($uuid ?? Uuid::uuid4());

        return new MailchimpCampaign($message);
    }

    private function mappingStub(): MailchimpObjectIdMapping
    {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn(self::MAIN_LIST_ID);
        $mapping->method('getListIdFromSource')->willReturnCallback(static function (?string $source): string {
            return 'elected_representative' === $source ? self::ELECTED_LIST_ID : self::MAIN_LIST_ID;
        });

        return $mapping;
    }
}
