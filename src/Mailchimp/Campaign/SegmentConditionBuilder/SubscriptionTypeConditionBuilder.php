<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter
            || \in_array($filter->getMessage()?->getInstanceScope(), [
                ScopeEnum::DEPUTY,
                ScopeEnum::ANIMATOR,
                ScopeEnum::SENATOR,
                ScopeEnum::LEGISLATIVE_CANDIDATE,
                ScopeEnum::CANDIDATE,
                ScopeEnum::CORRESPONDENT,
                ScopeEnum::REGIONAL_COORDINATOR,
            ], true);
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $interestKeys = [];

        switch ($scope = $campaign->getMessage()?->getInstanceScope()) {
            case ScopeEnum::NATIONAL_COMMUNICATION:
            case ScopeEnum::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION:
            case ScopeEnum::NATIONAL_TECH_DIVISION:
            case ScopeEnum::NATIONAL_FORMATION_DIVISION:
            case ScopeEnum::NATIONAL_IDEAS_DIVISION:
            case ScopeEnum::NATIONAL_TERRITORIES_DIVISION:
                return [];
            case ScopeEnum::REGIONAL_COORDINATOR:
            case ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY:
            case ScopeEnum::FDE_COORDINATOR:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case ScopeEnum::DEPUTY:
            case ScopeEnum::REGIONAL_DELEGATE:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;

            case ScopeEnum::LEGISLATIVE_CANDIDATE:
            case ScopeEnum::CANDIDATE:
            case ScopeEnum::MUNICIPAL_CANDIDATE:
            case ScopeEnum::MUNICIPAL_PILOT:
                if ($campaign->getMailchimpListType()) {
                    return [];
                }

                $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                break;

            case ScopeEnum::SENATOR:
                $interestKeys[] = SubscriptionTypeEnum::SENATOR_EMAIL;
                break;

            case ScopeEnum::ANIMATOR:
                $interestKeys[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                break;

            case ScopeEnum::CORRESPONDENT:
                if ($campaign->getMailchimpListType()) {
                    return [];
                }

                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;

            default:
                throw new \InvalidArgumentException(\sprintf('Message type %s does not match any subscription type', $scope));
        }

        return [$this->buildInterestCondition(
            $interestKeys,
            $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()
        )];
    }

    /**
     * @param AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        if (!$subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope = $filter->getScope()] ?? null) {
            throw new \InvalidArgumentException(\sprintf('Scope %s does not match any subscription type', $scope));
        }

        return [$this->buildInterestCondition(
            [$subscriptionType],
            $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()
        )];
    }
}
