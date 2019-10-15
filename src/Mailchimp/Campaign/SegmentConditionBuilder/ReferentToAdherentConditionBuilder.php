<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;

class ReferentToAdherentConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            && false === $filter->getContactOnlyVolunteers()
            && false === $filter->getContactOnlyRunningMates()
        ;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var ReferentUserFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        $conditions = [];

        if (
            $filter->includeCitizenProjectHosts()
            || $filter->includeCommitteeHosts()
            || $filter->includeCommitteeSupervisors()
            || $filter->includeAdherentsInCommittee()
            || $filter->includeAdherentsNoCommittee()
        ) {
            $interestKeys = [];

            if ($filter->includeCitizenProjectHosts()) {
                $interestKeys[] = Manager::INTEREST_KEY_CP_HOST;
            }

            if ($filter->includeCommitteeSupervisors()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
            }

            if ($filter->includeCommitteeHosts()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
            }

            if ($filter->includeAdherentsInCommittee()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_FOLLOWER;
            }

            if ($filter->includeAdherentsNoCommittee()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER;
            }

            $conditions[] = $this->buildInterestCondition($interestKeys, $this->mailchimpObjectIdMapping->getMemberGroupInterestGroupId(), false);
        }

        if ($filter->getInterests()) {
            $conditions[] = $this->buildInterestCondition($filter->getInterests(), $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId());
        }

        if (!$campaign->getStaticSegmentId()) {
            throw new StaticSegmentIdMissingException(sprintf(
                '[ReferentMessage] Referent message (%s) does not have a Mailchimp Static segment ID',
                $campaign->getMessage()->getUuid()->toString()
            ));
        }

        $conditions[] = $this->buildStaticSegmentCondition($campaign->getStaticSegmentId());

        return $conditions;
    }
}
