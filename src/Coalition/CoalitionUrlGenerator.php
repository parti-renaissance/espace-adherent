<?php

namespace App\Coalition;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Coalition\Cause;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;

class CoalitionUrlGenerator
{
    private const CAUSE_LINK_PATTERN = '%s/cause/%s';
    private const CAUSE_LIST_LINK_PATTERN = '%s/causes';
    private const CAUSE_EVENT_LINK_PATTERN = '%s/cause/%s?eventId=%s';
    private const COALITION_EVENT_LINK_PATTERN = '%s/coalition/%s?eventId=%s';
    private const CREATE_ACCOUNT_LINK_PATTERN = '%s/inscription';
    private const CREATE_PASSWORD_LINK_PATTERN = '%s/confirmation/%s/%s';

    private $coalitionsHost;

    public function __construct(string $coalitionsHost)
    {
        $this->coalitionsHost = $coalitionsHost;
    }

    public function generateHomepageLink(): string
    {
        return $this->coalitionsHost;
    }

    public function generateCauseLink(Cause $cause): string
    {
        return sprintf(self::CAUSE_LINK_PATTERN, $this->coalitionsHost, $cause->getUuid()->toString());
    }

    public function generateCauseListLink(): string
    {
        return sprintf(self::CAUSE_LIST_LINK_PATTERN, $this->coalitionsHost);
    }

    public function generateCreateAccountLink(): string
    {
        return sprintf(self::CREATE_ACCOUNT_LINK_PATTERN, $this->coalitionsHost);
    }

    public function generateCreatePasswordLink(Adherent $adherent, AdherentResetPasswordToken $token): string
    {
        return sprintf(self::CREATE_PASSWORD_LINK_PATTERN,
            $this->coalitionsHost,
            (string) $adherent->getUuid(),
            (string) $token->getValue()
        );
    }

    public function generateCauseEventLink(CauseEvent $event): string
    {
        return sprintf(
            self::CAUSE_EVENT_LINK_PATTERN,
            $this->coalitionsHost, $event->getCause()->getSlug(),
            $event->getUuid()->toString()
        );
    }

    public function generateCoalitionEventLink(CoalitionEvent $event): string
    {
        return sprintf(
            self::COALITION_EVENT_LINK_PATTERN,
            $this->coalitionsHost,
            $event->getCoalition()->getUuid(),
            $event->getUuid()->toString()
        );
    }
}
