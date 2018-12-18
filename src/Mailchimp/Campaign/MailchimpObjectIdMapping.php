<?php

namespace AppBundle\Mailchimp\Campaign;

class MailchimpObjectIdMapping
{
    private $folderIds;
    private $templateIds;

    public function __construct(array $folderIds, array $templateIds = [])
    {
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
    }

    public function getFolderIdForType(string $messageType): ?string
    {
        return $this->folderIds[$messageType] ?? null;
    }

    public function getTemplateIdForType(string $messageType): ?int
    {
        return $this->templateIds[$messageType] ?? null;
    }
}
