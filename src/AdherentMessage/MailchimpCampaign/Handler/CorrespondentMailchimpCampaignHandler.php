<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Membership\MembershipSourceEnum;
use App\Scope\ScopeEnum;

class CorrespondentMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return ScopeEnum::CORRESPONDENT === $message->getInstanceScope() && $message->getFilter() instanceof AudienceFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|AudienceFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        return [
            [
                [
                    'type' => self::MAILCHIMP_LIST_TYPE,
                    'value' => MembershipSourceEnum::JEMENGAGE,
                ],
            ],
            [
                [
                    'type' => self::MAILCHIMP_LIST_TYPE,
                    'value' => null, // for main list
                ],
            ],
        ];
    }
}
