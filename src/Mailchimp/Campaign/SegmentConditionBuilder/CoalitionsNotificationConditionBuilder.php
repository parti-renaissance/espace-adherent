<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\CoalitionsMessage;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\MemberRequest\CoalitionMemberRequestBuilder;

class CoalitionsNotificationConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilterInterface && $filter->getMessage() instanceof CoalitionsMessage;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return [$this->buildInterestCondition(
            [CoalitionMemberRequestBuilder::INTEREST_KEY_CAUSE_SUBSCRIPTION],
            $this->mailchimpObjectIdMapping->getCoalitionsNotificationInterestGroupId()
        )];
    }

    protected function getListInterestIds(): array
    {
        return $this->mailchimpObjectIdMapping->getCoalitionsInterestIds();
    }

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }
}
