<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Manager;

class AdherentInterestConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null === $campaign->getMailchimpListType()) {
            return $this->buildFromFilter($campaign->getMessage()->getFilter());
        }

        return [];
    }

    /**
     * @param ReferentUserFilter|AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];
        $interestIncludeKeys = [];
        $interestExcludeKeys = [];

        if (!$filter instanceof AudienceFilter) {
            if ($filter->includeAdherentsInCommittee() ^ $filter->includeAdherentsNoCommittee()) {
                $interestIncludeKeys[] = true === $filter->includeAdherentsNoCommittee() ? Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER : Manager::INTEREST_KEY_COMMITTEE_FOLLOWER;
            }

            // include interests
            if (true === $filter->includeCommitteeSupervisors()) {
                $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
            }

            if (true === $filter->includeCommitteeHosts()) {
                $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
            }

            // exclude interests
            if (false === $filter->includeCommitteeSupervisors()) {
                $interestExcludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
            }

            if (false === $filter->includeCommitteeHosts()) {
                $interestExcludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
            }
        }

        // add conditions
        if ($interestIncludeKeys) {
            $conditions[] = $this->buildInterestCondition($interestIncludeKeys, $this->mailchimpObjectIdMapping->getMemberGroupInterestGroupId(), self::OP_INTEREST_ONE);
        }

        if ($interestExcludeKeys) {
            $conditions[] = $this->buildInterestCondition($interestExcludeKeys, $this->mailchimpObjectIdMapping->getMemberGroupInterestGroupId(), self::OP_INTEREST_NONE);
        }

        if ($filter->getInterests()) {
            $conditions[] = $this->buildInterestCondition($filter->getInterests(), $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId());
        }

        return $conditions;
    }
}
