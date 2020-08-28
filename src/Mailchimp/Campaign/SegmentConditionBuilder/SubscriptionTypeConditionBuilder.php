<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentTerritorialCouncilMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return \in_array(\get_class($filter->getMessage()), [
            ReferentAdherentMessage::class,
            ReferentTerritorialCouncilMessage::class,
            DeputyAdherentMessage::class,
            CommitteeAdherentMessage::class,
            CitizenProjectAdherentMessage::class,
            MunicipalChiefAdherentMessage::class,
            SenatorAdherentMessage::class,
            LegislativeCandidateAdherentMessage::class,
        ], true);
    }

    public function build(MailchimpCampaign $campaign): array
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
            case ReferentTerritorialCouncilMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::REFERENT_EMAIL;
                break;
            case DeputyAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
                break;

            case LegislativeCandidateAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::CANDIDATE_EMAIL;
                break;

            case SenatorAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::SENATOR_EMAIL;
                break;

            case CommitteeAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                break;

            case CitizenProjectAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL;
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
}
