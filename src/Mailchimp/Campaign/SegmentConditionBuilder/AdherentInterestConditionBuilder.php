<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Manager;

class AdherentInterestConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentZoneFilter
            || (
                $filter instanceof ReferentUserFilter
                    && false === $filter->getContactOnlyVolunteers()
                    && false === $filter->getContactOnlyRunningMates()
            )
        ;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var AdherentZoneFilter|ReferentUserFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        $conditions = [];
        $interestIncludeKeys = [];
        $interestExcludeKeys = [];

        // include interests
        if (true === $filter->includeCitizenProjectHosts()) {
            $interestIncludeKeys[] = Manager::INTEREST_KEY_CP_HOST;
        }

        if (true === $filter->includeCommitteeSupervisors()) {
            $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
        }

        if (true === $filter->includeCommitteeHosts()) {
            $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
        }

        if (true === $filter->includeAdherentsInCommittee()) {
            $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_FOLLOWER;
        }

        if (true === $filter->includeAdherentsNoCommittee()) {
            $interestIncludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER;
        }

        // exclude interests
        if (false === $filter->includeCitizenProjectHosts()) {
            $interestExcludeKeys[] = Manager::INTEREST_KEY_CP_HOST;
        }

        if (false === $filter->includeCommitteeSupervisors()) {
            $interestExcludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
        }

        if (false === $filter->includeCommitteeHosts()) {
            $interestExcludeKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
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
