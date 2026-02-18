<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\AudienceTypeEnum;
use App\Scope\ScopeEnum;

class LegislativeCandidateMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return ScopeEnum::LEGISLATIVE_CANDIDATE === $message->getInstanceScope() && $message->getFilter() instanceof AdherentMessageFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|AdherentMessageFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $audienceType = $filter->getAudienceType();
        $conditions = [];

        if (null === $audienceType || AudienceTypeEnum::ADHERENT === $audienceType) {
            $conditions[] = [[
                'type' => self::MAILCHIMP_LIST_TYPE,
                'value' => null, // for main list
            ]];
        }

        return $conditions;
    }
}
