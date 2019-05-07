<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\ReferentTag;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;
use AppBundle\Subscription\SubscriptionTypeEnum;

class SegmentConditionsBuilder
{
    private $interestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;

    public function __construct(
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId
    ) {
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
    }

    public function build(AdherentMessageInterface $message): array
    {
        $conditions[] = $this->buildSubscriptionTypeCondition($message);

        $filter = $message->getFilter();

        if ($filter instanceof ReferentUserFilter) {
            $conditions = array_merge($conditions, $this->buildReferentConditions($filter));
        } elseif ($filter instanceof AdherentZoneFilter) {
            $conditions[] = $this->buildReferentZoneCondition($filter->getReferentTag());
        } elseif ($filter instanceof CommitteeFilter) {
            $conditions[] = $this->buildCommitteeFilterCondition($filter->getCommittee());
        } elseif ($filter instanceof CitizenProjectFilter) {
            $conditions[] = $this->buildCitizenProjectFilterCondition($filter->getCitizenProject());
        }

        return [
            'match' => 'all',
            'conditions' => $conditions ?? [],
        ];
    }

    private function buildSubscriptionTypeCondition(AdherentMessageInterface $message, bool $matchAll = true): array
    {
        $interestKeys = [];

        switch ($messageClass = \get_class($message)) {
            case ReferentAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case DeputyAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;
            case CommitteeAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                break;
            case CitizenProjectAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Message type %s does not match any subscription type', $messageClass));
        }

        return $this->buildInterestCondition($interestKeys, $this->subscriptionTypeInterestGroupId, $matchAll);
    }

    private function buildInterestCondition(array $interestKeys, string $groupId, bool $matchAll = true): array
    {
        return [
            'condition_type' => 'Interests',
            'op' => $matchAll ? 'interestcontainsall' : 'interestcontains',
            'field' => sprintf('interests-%s', $groupId),
            'value' => array_values(
                array_intersect_key($this->interestIds, array_fill_keys($interestKeys, true))
            ),
        ];
    }

    private function buildReferentConditions(ReferentUserFilter $filter): array
    {
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

            $conditions[] = $this->buildInterestCondition($interestKeys, $this->memberGroupInterestGroupId, false);
        }

        if ($filter->getGender()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'GENDER',
                'value' => $filter->getGender(),
            ];
        }

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $minAge))->format('Y-m-d'),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format('Y-m-d'),
            ];
        }

        if ($filter->getFirstName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'FIRST_NAME',
                'value' => $filter->getFirstName(),
            ];
        }

        if ($filter->getLastName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'LAST_NAME',
                'value' => $filter->getLastName(),
            ];
        }

        if ($filter->getCity()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => 'CITY',
                'value' => $filter->getCity(),
            ];
        }

        if ($filter->getInterests()) {
            $conditions[] = $this->buildInterestCondition($filter->getInterests(), $this->memberInterestInterestGroupId);
        }

        $conditions[] = $this->buildReferentZoneCondition($filter->getReferentTag());

        return $conditions;
    }

    private function buildReferentZoneCondition(ReferentTag $tag): array
    {
        if (!$tag->getExternalId()) {
            throw new \InvalidArgumentException(
                sprintf('[AdherentMessage] Referent tag (%s) does not have a Mailchimp ID', $tag->getCode())
            );
        }

        return $this->buildStaticSegmentCondition($tag->getExternalId());
    }

    private function buildCommitteeFilterCondition(?Committee $committee): array
    {
        if (!$committee) {
            throw new InvalidFilterException('[AdherentMessage] Committee should not be empty');
        }

        if (!$committee->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(
                sprintf('[AdherentMessage] Committee "%s" does not have mailchimp ID', $committee->getUuidAsString())
            );
        }

        return $this->buildStaticSegmentCondition($committee->getMailchimpId());
    }

    private function buildCitizenProjectFilterCondition(?CitizenProject $citizenProject): array
    {
        if (!$citizenProject) {
            throw new InvalidFilterException('[AdherentMessage] Citizen project should not be empty');
        }

        if (!$citizenProject->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(
                sprintf('[AdherentMessage] Citizen project "%s" does not have mailchimp ID', $citizenProject->getUuidAsString())
            );
        }

        return $this->buildStaticSegmentCondition($citizenProject->getMailchimpId());
    }

    private function buildStaticSegmentCondition(int $externalId): array
    {
        return [
            'condition_type' => 'StaticSegment',
            'op' => 'static_is',
            'field' => 'static_segment',
            'value' => $externalId,
        ];
    }
}
