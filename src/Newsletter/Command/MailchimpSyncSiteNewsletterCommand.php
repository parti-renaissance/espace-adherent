<?php

namespace AppBundle\Newsletter\Command;

class MailchimpSyncSiteNewsletterCommand
{
    private $email;
    private $siteCode;
    private $type;

    public function __construct(string $email, string $siteCode, string $type)
    {
        $this->email = $email;
        $this->siteCode = $siteCode;
        $this->type = $type;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSiteCode(): string
    {
        return $this->siteCode;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
