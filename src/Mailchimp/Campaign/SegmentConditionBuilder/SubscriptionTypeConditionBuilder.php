<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return \in_array(\get_class($filter->getMessage()), [
            ReferentAdherentMessage::class,
            DeputyAdherentMessage::class,
            CommitteeAdherentMessage::class,
            CitizenProjectAdherentMessage::class,
            MunicipalChiefAdherentMessage::class,
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

            case DeputyAdherentMessage::class:
                $interestKeys[] = SubscriptionTypeEnum::DEPUTY_EMAIL;
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
                    $interestKeys[] = SubscriptionTypeEnum::MUNICIPAL_EMAIL;
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
