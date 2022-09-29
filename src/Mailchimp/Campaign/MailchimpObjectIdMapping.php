<?php

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AdherentMessageInterface;

class MailchimpObjectIdMapping
{
    private $mainListId;
    private $newsletterListId;
    private $electedRepresentativeListId;
    private $applicationRequestCandidateListId;
    private $jecouteListId;
    private $jeMengageListId;
    private $coalitionsListId;
    private $newsletterLegislativeCandidateListId;
    private $newsletterRenaissanceListId;
    private $folderIds;
    private $templateIds;
    private $interestIds;
    private $coalitionsInterestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;
    private $coalitionsNotificationInterestGroupId;
    private $applicationRequestTagIds;
    private $newsletterTagIds;

    public function __construct(
        string $mainListId,
        string $newsletterListId,
        string $electedRepresentativeListId,
        string $applicationRequestCandidateListId,
        string $jecouteListId,
        string $jeMengageListId,
        string $coalitionsListId,
        string $newsletterLegislativeCandidateListId,
        string $newsletterRenaissanceListId,
        array $folderIds,
        array $templateIds,
        array $interestIds,
        array $coalitionsInterestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId,
        string $coalitionsNotificationInterestGroupId,
        array $applicationRequestTagIds,
        array $newsletterTagIds
    ) {
        $this->mainListId = $mainListId;
        $this->newsletterListId = $newsletterListId;
        $this->electedRepresentativeListId = $electedRepresentativeListId;
        $this->applicationRequestCandidateListId = $applicationRequestCandidateListId;
        $this->jecouteListId = $jecouteListId;
        $this->jeMengageListId = $jeMengageListId;
        $this->coalitionsListId = $coalitionsListId;
        $this->newsletterLegislativeCandidateListId = $newsletterLegislativeCandidateListId;
        $this->newsletterRenaissanceListId = $newsletterRenaissanceListId;
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
        $this->interestIds = $interestIds;
        $this->coalitionsInterestIds = $coalitionsInterestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
        $this->coalitionsNotificationInterestGroupId = $coalitionsNotificationInterestGroupId;
        $this->applicationRequestTagIds = $applicationRequestTagIds;
        $this->newsletterTagIds = $newsletterTagIds;
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

    public function getCoalitionsInterestIds(): array
    {
        return $this->coalitionsInterestIds;
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

    public function getCoalitionsNotificationInterestGroupId(): string
    {
        return $this->coalitionsNotificationInterestGroupId;
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

    public function getCoalitionsListId(): string
    {
        return $this->coalitionsListId;
    }

    public function getListIdByMessageType(string $messageType): string
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return $this->applicationRequestCandidateListId;
            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return $this->jecouteListId;
            case AdherentMessageTypeEnum::COALITIONS:
                return $this->coalitionsListId;
            default:
                return $this->mainListId;
        }
    }

    public function getApplicationRequestTagIds(): array
    {
        return $this->applicationRequestTagIds;
    }

    public function getNewsletterTagIds(): array
    {
        return $this->newsletterTagIds;
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

    private function findTemplateId(string $key): ?int
    {
        return $this->templateIds[$key] ?? null;
    }
}
