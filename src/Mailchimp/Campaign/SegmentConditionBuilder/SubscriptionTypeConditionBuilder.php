<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\FdeCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\PresidentDepartmentalAssemblyAdherentMessage;
use App\Entity\AdherentMessage\RegionalCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\RegionalDelegateAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter
            || \in_array(\get_class($filter->getMessage()), [
                DeputyAdherentMessage::class,
                CommitteeAdherentMessage::class,
                SenatorAdherentMessage::class,
                LegislativeCandidateAdherentMessage::class,
                CandidateAdherentMessage::class,
                CorrespondentAdherentMessage::class,
                RegionalCoordinatorAdherentMessage::class,
            ], true);
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $interestKeys = [];

        switch ($messageClass = \get_class($campaign->getMessage())) {
            case RegionalCoordinatorAdherentMessage::class:
            case PresidentDepartmentalAssemblyAdherentMessage::class:
            case FdeCoordinatorAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case DeputyAdherentMessage::class:
            case RegionalDelegateAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;

            case LegislativeCandidateAdherentMessage::class:
            case CandidateAdherentMessage::class:
                if ($campaign->getMailchimpListType()) {
                    return [];
                }

                $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                break;

            case SenatorAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::SENATOR_EMAIL;
                break;

            case CommitteeAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                break;

            case CorrespondentAdherentMessage::class:
                if ($campaign->getMailchimpListType()) {
                    return [];
                }

                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;

            default:
                throw new \InvalidArgumentException(\sprintf('Message type %s does not match any subscription type', $messageClass));
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
