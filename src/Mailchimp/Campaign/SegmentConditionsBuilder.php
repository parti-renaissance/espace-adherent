<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\DynamicSegmentInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SegmentConditionBuilderInterface;

class SegmentConditionsBuilder
{
    /** @var iterable<SegmentConditionBuilderInterface> */
    private iterable $builders;

    public function __construct(
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        iterable $builders,
    ) {
        $this->builders = $builders;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $segmentId = $campaign->getStaticSegmentId();
        if (null === $segmentId) {
            return [];
        }

        return [
            'list_id' => $this->getListId($campaign),
            'segment_opts' => [
                'saved_segment_id' => $segmentId,
            ],
        ];
    }

    public function buildFromDynamicSegment(DynamicSegmentInterface $dynamicSegment): array
    {
        $filter = $dynamicSegment->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Filter is null');
        }

        $conditions = [];
        $built = false;

        foreach ($this->builders as $builder) {
            if ($builder->support($filter)) {
                $conditions = array_merge($conditions, $builder->buildFromFilter($filter));
                $built = true;
            }
        }

        if (!$built) {
            throw new \RuntimeException(\sprintf('Any builder was found for the filter class: %s', $filter::class));
        }

        return [
            'match' => 'all',
            'conditions' => $conditions,
        ];
    }

    private function getListId(MailchimpCampaign $campaign): string
    {
        if ($campaign->getMailchimpListType()) {
            return $this->mailchimpObjectIdMapping->getListIdFromSource($campaign->getMailchimpListType());
        }

        return $this->mailchimpObjectIdMapping->getMainListId();
    }
}
