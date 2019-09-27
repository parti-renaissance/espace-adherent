<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

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

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $minAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($registeredSince = $filter->getRegisteredSince()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
                'value' => $registeredSince->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
                'value' => $registeredUntil->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($city = $campaign->getCity()) {
            $field = is_numeric($city[0])
                ? MemberRequest::MERGE_FIELD_ZIP_CODE
                : MemberRequest::MERGE_FIELD_CITY
            ;

            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'starts',
                'field' => $field,
                'value' => $city,
            ];
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
