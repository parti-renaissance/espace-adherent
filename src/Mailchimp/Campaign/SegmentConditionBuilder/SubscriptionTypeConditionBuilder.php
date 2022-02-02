<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter ||
            \in_array(\get_class($filter->getMessage()), [
            ReferentAdherentMessage::class,
            ReferentInstancesMessage::class,
            DeputyAdherentMessage::class,
            CommitteeAdherentMessage::class,
            MunicipalChiefAdherentMessage::class,
            SenatorAdherentMessage::class,
            LegislativeCandidateAdherentMessage::class,
            CandidateAdherentMessage::class,
            CorrespondentAdherentMessage::class,
        ], true);
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $interestKeys = [];

        switch ($messageClass = \get_class($campaign->getMessage())) {
            case ReferentAdherentMessage::class:
                if (
                    ($filter = $campaign->getMessage()->getFilter())
                    && ($filter->getContactOnlyRunningMates() || $filter->getContactOnlyVolunteers())
                ) {
                    return [];
                }

                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case ReferentInstancesMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case DeputyAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;

            case LegislativeCandidateAdherentMessage::class:
            case CandidateAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                break;

            case SenatorAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::SENATOR_EMAIL;
                break;

            case CommitteeAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                break;

            case CorrespondentAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;

            case MunicipalChiefAdherentMessage::class:
                /** @var MunicipalChiefFilter $filter */
                if (($filter = $campaign->getMessage()->getFilter()) && $filter->getContactAdherents()) {
                    $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                    break;
                }

                return [];

            default:
                throw new \InvalidArgumentException(sprintf('Message type %s does not match any subscription type', $messageClass));
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
        switch ($scope = $filter->getScope()) {
            case ScopeEnum::REFERENT:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case ScopeEnum::DEPUTY:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;
            case ScopeEnum::CANDIDATE:
                $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                break;
            case ScopeEnum::SENATOR:
                $interestKeys[] = SubscriptionTypeEnum::SENATOR_EMAIL;
                break;
            case ScopeEnum::CORRESPONDENT:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Scope %s does not match any subscription type', $scope));
        }

        return [$this->buildInterestCondition(
            $interestKeys,
            $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()
        )];
    }
}
