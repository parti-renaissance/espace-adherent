<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Adherent\Tag\TagFilterEnum;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
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

        if ($filter->adherentTags) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => $filter->adherentTags,
            ];
        }

        if ($filter->electTags) {
            foreach (TagFilterEnum::getFiltersTagsMapping()[$filter->electTags] ?? [] as $tag => $isEnabled) {
                $conditions[] = [
                    'condition_type' => 'TextMerge',
                    'op' => $isEnabled ? 'contains' : 'notcontain',
                    'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                    'value' => $tag,
                ];
            }
        }

        return $conditions;
    }
}
