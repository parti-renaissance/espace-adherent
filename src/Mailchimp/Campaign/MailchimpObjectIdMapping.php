<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;

class MailchimpObjectIdMapping
{
    private $mainListId;
    private $newsletterListId;
    private $applicationRequestCandidateListId;
    private $folderIds;
    private $templateIds;
    private $interestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;

    public function __construct(
        string $mainListId,
        string $newsletterListId,
        string $applicationRequestCandidateListId,
        array $folderIds,
        array $templateIds,
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId
    ) {
        $this->mainListId = $mainListId;
        $this->newsletterListId = $newsletterListId;
        $this->applicationRequestCandidateListId = $applicationRequestCandidateListId;
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
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

    public function getApplicationRequestCandidateListId(): string
    {
        return $this->applicationRequestCandidateListId;
    }

    public function getListIdByMessageType(string $messageType): string
    {
        if (AdherentMessageTypeEnum::MUNICIPAL_CHIEF === $messageType) {
            return $this->applicationRequestCandidateListId;
        }

        return $this->mainListId;
    }
}
