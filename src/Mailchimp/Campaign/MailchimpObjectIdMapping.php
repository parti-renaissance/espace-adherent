<?php

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AdherentMessageInterface;

class MailchimpObjectIdMapping
{
    public function __construct(
        private readonly string $mainListId,
        private readonly string $newsletterListId,
        private readonly string $electedRepresentativeListId,
        private readonly string $applicationRequestCandidateListId,
        private readonly string $jecouteListId,
        private readonly string $jeMengageListId,
        private readonly string $newsletterLegislativeCandidateListId,
        private readonly string $newsletterRenaissanceListId,
        private readonly array $folderIds,
        private readonly array $templateIds,
        private readonly array $interestIds,
        private readonly string $memberGroupInterestGroupId,
        private readonly string $memberInterestInterestGroupId,
        private readonly string $subscriptionTypeInterestGroupId,
        private readonly array $applicationRequestTagIds,
        private readonly string $mailchimpCampaignUrl,
        private readonly string $mailchimpOrg,
    ) {
    }

    public function getFolderIdByType(string $messageType): ?string
    {
        return $this->folderIds[$messageType] ?? null;
    }

    public function getTemplateId(AdherentMessageInterface $message): ?int
    {
        if (AdherentMessageInterface::SOURCE_API === $message->getSource()) {
            if (!$templateId = $this->findTemplateId(sprintf('%s_api', $message->getType()))) {
                $templateId = $this->findTemplateId('default_api');
            }
        }

        return $templateId ?? $this->findTemplateId($message->getType());
    }

    public function getInterestIds(): array
    {
        return $this->interestIds;
    }

    public function getMemberGroupInterestGroupId(): string
    {
        return $this->memberGroupInterestGroupId;
    }

    public function getMemberInterestInterestGroupId(): string
    {
        return $this->memberInterestInterestGroupId;
    }

    public function getSubscriptionTypeInterestGroupId(): string
    {
        return $this->subscriptionTypeInterestGroupId;
    }

    public function getMainListId(): string
    {
        return $this->mainListId;
    }

    public function getNewsletterListId(): string
    {
        return $this->newsletterListId;
    }

    public function getNewsletterLegislativeCandidateListId(): string
    {
        return $this->newsletterLegislativeCandidateListId;
    }

    public function getNewsletterRenaissanceListId(): string
    {
        return $this->newsletterRenaissanceListId;
    }

    public function getElectedRepresentativeListId(): string
    {
        return $this->electedRepresentativeListId;
    }

    public function getApplicationRequestCandidateListId(): string
    {
        return $this->applicationRequestCandidateListId;
    }

    public function getJecouteListId(): string
    {
        return $this->jecouteListId;
    }

    public function getListIdByMessageType(string $messageType): string
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return $this->jecouteListId;
            default:
                return $this->mainListId;
        }
    }

    public function getApplicationRequestTagIds(): array
    {
        return $this->applicationRequestTagIds;
    }

    public function getJeMengageListId(): string
    {
        return $this->jeMengageListId;
    }

    public function getListIdFromSource(?string $source): string
    {
        switch ($source) {
            case AudienceTypeEnum::JEMENGAGE:
                return $this->getJeMengageListId();
            case AudienceTypeEnum::LEGISLATIVE_CANDIDATE_NEWSLETTER:
                return $this->getNewsletterLegislativeCandidateListId();
            default:
                return $this->getMainListId();
        }
    }

    public function generateMailchimpPreviewLink(?string $campaignId): ?string
    {
        if (!$campaignId) {
            return null;
        }

        return sprintf('%s?u=%s&id=%s', $this->mailchimpCampaignUrl, $this->mailchimpOrg, $campaignId);
    }

    private function findTemplateId(string $key): ?int
    {
        return $this->templateIds[$key] ?? null;
    }
}
