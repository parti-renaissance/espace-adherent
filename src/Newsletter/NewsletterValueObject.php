<?php

declare(strict_types=1);

namespace App\Newsletter;

use App\Entity\Geo\Zone;
use App\Entity\LegislativeNewsletterSubscription;
use App\Entity\NewsletterSubscription;
use App\Entity\Renaissance\NewsletterSubscription as RenaissanceNewsletterSubscription;
use App\Newsletter\Command\MailchimpSyncSiteNewsletterCommand;

class NewsletterValueObject
{
    private $email;
    private $zipCode;
    private $firstName;
    private $lastName;
    private $countryName;
    private $siteCode;
    private $type;
    private $subscribed = true;
    private $zones = [];

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getSiteCode(): ?string
    {
        return $this->siteCode;
    }

    /** @return Zone[] */
    public function getZones(): array
    {
        return $this->zones;
    }

    public function getType(): ?string
    {
        return $this->type;
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
            : NewsletterTypeEnum::MAIN_SITE;

        return $object;
    }

    public static function createFromRenaissanceNewsletterSubscription(RenaissanceNewsletterSubscription $newsletter): self
    {
        $object = new self();

        $object->email = $newsletter->getEmail();
        $object->zipCode = $newsletter->zipCode;
        $object->firstName = $newsletter->firstName;
        $object->lastName = $newsletter->lastName;
        $object->type = $newsletter->source ?? NewsletterTypeEnum::SITE_RENAISSANCE;
        $object->subscribed = $newsletter->isConfirmed();

        return $object;
    }

    public static function createFromSiteNewsletterCommand(MailchimpSyncSiteNewsletterCommand $command): self
    {
        if (!NewsletterTypeEnum::isValid($command->getType())) {
            throw new \InvalidArgumentException(\sprintf('[Newsletter] site type "%s" of newsletter is invalid', $command->getType()));
        }

        $object = new self();

        $object->email = $command->getEmail();
        $object->siteCode = $command->getSiteCode();
        $object->type = $command->getType();

        return $object;
    }

    public static function createFromLegislativeNewsletterCommand(LegislativeNewsletterSubscription $subscription): self
    {
        $object = new self();

        $object->email = $subscription->getEmailAddress();
        $object->zones = $subscription->getFromZones()->toArray();
        $object->type = NewsletterTypeEnum::SITE_LEGISLATIVE_CANDIDATE;

        return $object;
    }
}
