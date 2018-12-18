<?php

namespace AppBundle\Mailchimp\Campaign;

class MailchimpObjectIdMapping
{
    private $folderIds;
    private $templateIds;

    public function __construct(array $folderIds = [], array $templateIds = [])
    {
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
    }

    public function getFolderIdByType(string $messageType): ?string
    {
        return $this->folderIds[$messageType] ?? null;
    }

    public function getTemplateIdByType(string $messageType): ?int
    {
        return $this->templateIds[$messageType] ?? null;
    }
}
