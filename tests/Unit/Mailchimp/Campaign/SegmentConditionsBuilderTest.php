<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign;

use App\AdherentMessage\DynamicSegmentInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SegmentConditionBuilderInterface;
use App\Mailchimp\Campaign\SegmentConditionsBuilder;
use PHPUnit\Framework\TestCase;

class SegmentConditionsBuilderTest extends TestCase
{
    private const MAIN_LIST_ID = 'list-main-abc';

    public function testBuildFromMailchimpCampaignWithStaticSegmentIdReturnsSavedSegmentPayload(): void
    {
        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);
        $campaign->setStaticSegmentId(12345);

        $builder = new SegmentConditionsBuilder($this->mockObjectIdMappingReturningMainList(), []);

        $opts = $builder->buildFromMailchimpCampaign($campaign);

        self::assertSame([
            'list_id' => self::MAIN_LIST_ID,
            'segment_opts' => [
                'saved_segment_id' => 12345,
            ],
        ], $opts);
    }

    public function testBuildFromMailchimpCampaignWithoutStaticSegmentIdReturnsEmptyPayload(): void
    {
        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);
        // staticSegmentId left at null — campaign not yet prepared

        $builder = new SegmentConditionsBuilder($this->mockObjectIdMappingReturningMainList(), []);

        self::assertSame([], $builder->buildFromMailchimpCampaign($campaign));
    }

    public function testBuildFromMailchimpCampaignDoesNotIterateBuilders(): void
    {
        $message = $this->createStub(AdherentMessageInterface::class);
        $campaign = new MailchimpCampaign($message);
        $campaign->setStaticSegmentId(7);

        $conditionBuilder = $this->createMock(SegmentConditionBuilderInterface::class);
        $conditionBuilder->expects(self::never())->method('support');
        $conditionBuilder->expects(self::never())->method('buildFromFilter');

        $builder = new SegmentConditionsBuilder($this->mockObjectIdMappingReturningMainList(), [$conditionBuilder]);

        $opts = $builder->buildFromMailchimpCampaign($campaign);

        self::assertSame(['saved_segment_id' => 7], $opts['segment_opts']);
    }

    public function testBuildFromDynamicSegmentIteratesSupportingBuilders(): void
    {
        $filter = $this->createStub(SegmentFilterInterface::class);

        $segment = $this->createStub(DynamicSegmentInterface::class);
        $segment->method('getFilter')->willReturn($filter);

        $supporting = $this->createMock(SegmentConditionBuilderInterface::class);
        $supporting->expects(self::once())->method('support')->with($filter)->willReturn(true);
        $supporting->expects(self::once())
            ->method('buildFromFilter')
            ->with($filter)
            ->willReturn([['condition_type' => 'TextMerge', 'op' => 'is', 'field' => 'X', 'value' => 'a']]);

        $skipping = $this->createMock(SegmentConditionBuilderInterface::class);
        $skipping->expects(self::once())->method('support')->with($filter)->willReturn(false);
        $skipping->expects(self::never())->method('buildFromFilter');

        $builder = new SegmentConditionsBuilder($this->mockObjectIdMappingReturningMainList(), [$supporting, $skipping]);

        self::assertSame([
            'match' => 'all',
            'conditions' => [['condition_type' => 'TextMerge', 'op' => 'is', 'field' => 'X', 'value' => 'a']],
        ], $builder->buildFromDynamicSegment($segment));
    }

    public function testBuildFromDynamicSegmentThrowsWhenNoBuilderSupportsTheFilter(): void
    {
        $filter = $this->createStub(SegmentFilterInterface::class);

        $segment = $this->createStub(DynamicSegmentInterface::class);
        $segment->method('getFilter')->willReturn($filter);

        $unsupporting = $this->createStub(SegmentConditionBuilderInterface::class);
        $unsupporting->method('support')->willReturn(false);

        $builder = new SegmentConditionsBuilder($this->mockObjectIdMappingReturningMainList(), [$unsupporting]);

        $this->expectException(\RuntimeException::class);

        $builder->buildFromDynamicSegment($segment);
    }

    private function mockObjectIdMappingReturningMainList(): MailchimpObjectIdMapping
    {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn(self::MAIN_LIST_ID);

        return $mapping;
    }
}
