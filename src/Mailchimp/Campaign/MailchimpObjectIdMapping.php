<?php

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\AdherentMessageTypeEnum;

class MailchimpObjectIdMapping
{
    private $mainListId;
    private $newsletterListId;
    private $electedRepresentativeListId;
    private $applicationRequestCandidateListId;
    private $jecouteListId;
    private $folderIds;
    private $templateIds;
    private $interestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;
    private $applicationRequestTagIds;
    private $newsletterTagIds;

    public function __construct(
        string $mainListId,
        string $newsletterListId,
        string $electedRepresentativeListId,
        string $applicationRequestCandidateListId,
        string $jecouteListId,
        array $folderIds,
        array $templateIds,
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId,
        array $applicationRequestTagIds,
        array $newsletterTagIds
    ) {
        $this->mainListId = $mainListId;
        $this->newsletterListId = $newsletterListId;
        $this->electedRepresentativeListId = $electedRepresentativeListId;
        $this->applicationRequestCandidateListId = $applicationRequestCandidateListId;
        $this->jecouteListId = $jecouteListId;
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
        $this->applicationRequestTagIds = $applicationRequestTagIds;
        $this->newsletterTagIds = $newsletterTagIds;
    }

    public function getFolderIdByType(string $messageType): ?string
    {
        return $this->folderIds[$messageType] ?? null;
    }

    public function getTemplateIdByType(string $messageType): ?int
    {
        return $this->templateIds[$messageType] ?? null;
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
            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return $this->applicationRequestCandidateListId;
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

    public function getNewsletterTagIds(): array
    {
        return $this->newsletterTagIds;
    }
}
