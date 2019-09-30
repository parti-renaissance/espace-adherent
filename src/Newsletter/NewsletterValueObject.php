<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Newsletter\Command\MailchimpSyncSiteNewsletterCommand;

class NewsletterValueObject
{
    private $email;
    private $zipCode;
    private $countryName;
    private $siteCode;
    private $type;
    private $subscribed = true;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    public function setSubscribed(bool $subscribed): void
    {
        $this->subscribed = $subscribed;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): void
    {
        $this->countryName = $countryName;
    }

    public function getSiteCode(): ?string
    {
        return $this->siteCode;
    }

    public function setSiteCode(?string $siteCode): void
    {
        $this->siteCode = $siteCode;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public static function createFromNewsletterSubscription(NewsletterSubscription $newsletter): self
    {
        $object = new self();

        $object->email = $newsletter->getEmail();
        $object->zipCode = $newsletter->getPostalCode();
        $object->countryName = $newsletter->getCountryName();
        $object->subscribed = $newsletter->isNotDeleted();

        $object->type = $newsletter->isFromEvent() ?
            NewsletterTypeEnum::MAIN_SITE_FROM_EVENT
            : NewsletterTypeEnum::MAIN_SITE
        ;

        return $object;
    }

    public static function createFromSiteNewsletterCommand(MailchimpSyncSiteNewsletterCommand $command): self
    {
        if (!NewsletterTypeEnum::isValid($command->getType())) {
            throw new \InvalidArgumentException(
                sprintf('[Newsletter] site type "%s" of newsletter is invalid', $command->getType())
            );
        }

        $object = new self();

        $object->email = $command->getEmail();
        $object->siteCode = $command->getSiteCode();
        $object->type = $command->getType();

        return $object;
    }
}
