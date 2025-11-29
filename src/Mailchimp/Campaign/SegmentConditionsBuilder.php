<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\DynamicSegmentInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AbstractElectedRepresentativeFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SegmentConditionBuilderInterface;

class SegmentConditionsBuilder
{
    private $mailchimpObjectIdMapping;
    /** @var SegmentConditionBuilderInterface[] */
    private $builders;

    public function __construct(MailchimpObjectIdMapping $mailchimpObjectIdMapping, iterable $builders)
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->builders = $builders;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $message = $campaign->getMessage();
        $filter = $message->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Filter is null');
        }

        $savedSegment = [];
        $conditions = [];
        if ($segment = $filter->getSegment()) {
            if (!$segment->isSynchronized()) {
                throw new \RuntimeException(\sprintf('The segment with id "%s" of the filter class %s is not syncronized.', $segment->getId(), $filter::class));
            }

            $savedSegment = ['saved_segment_id' => $filter->getSegment()->getMailchimpId()];
        } else {
            foreach ($this->builders as $builder) {
                if ($builder->support($filter)) {
                    $conditions = array_merge($conditions, $builder->buildFromMailchimpCampaign($campaign));
                    $built = true;
                }
            }

            if (!isset($built)) {
                throw new \RuntimeException(\sprintf('Any builder was found for the filter class: %s', $filter::class));
            }
        }

        return [
            'list_id' => $this->getListId($message, $campaign),
            'segment_opts' => array_merge($savedSegment, [
                'match' => 'all',
                'conditions' => $conditions,
            ]),
        ];
    }

    public function buildFromDynamicSegment(DynamicSegmentInterface $dynamicSegment): array
    {
        $filter = $dynamicSegment->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Filter is null');
        }

        $conditions = [];

        foreach ($this->builders as $builder) {
            if ($builder->support($filter)) {
                $conditions = array_merge($conditions, $builder->buildFromFilter($filter));
                $built = true;
            }
        }

        if (!isset($built)) {
            throw new \RuntimeException(\sprintf('Any builder was found for the filter class: %s', $filter::class));
        }

        return [
            'match' => 'all',
            'conditions' => $conditions,
        ];
    }

    private function getListId(AdherentMessageInterface $message, MailchimpCampaign $campaign): string
    {
        if ($filter = $message->getFilter()) {
            if ($filter instanceof AbstractElectedRepresentativeFilter) {
                return $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();
            }
        }

        if ($campaign->getMailchimpListType()) {
            return $this->mailchimpObjectIdMapping->getListIdFromSource($campaign->getMailchimpListType());
        }

        return $this->mailchimpObjectIdMapping->getMainListId();
    }
}
