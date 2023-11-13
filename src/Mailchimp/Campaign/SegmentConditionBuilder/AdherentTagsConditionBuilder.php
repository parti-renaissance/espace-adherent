<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Adherent\Tag\TagEnum;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidAdherentTagValueException;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentTagsConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null !== $campaign->getMailchimpListType()) {
            return [];
        }

        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        if ($filter->adherentTags && $tagValue = $this->transformTagValue($filter->adherentTags)) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => $tagValue,
            ];
        }

        if ($filter->electTags && $tagValue = $this->transformTagValue($filter->electTags)) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => $tagValue,
            ];
        }

        return $conditions;
    }

    private function transformTagValue(string $tag): ?string
    {
        if ($tagValue = TagEnum::getMCTagLabels()[$tag]) {
            return $tagValue;
        }

        throw new InvalidAdherentTagValueException($tag);
    }
}
