<?php

declare(strict_types=1);

namespace App\Newsletter;

use App\Entity\Adherent;
use App\Entity\NewsletterSubscriptionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class NewsletterEvent extends Event
{
    private $newsletter;
    private $adherent;

    public function __construct(NewsletterSubscriptionInterface $newsletter, ?Adherent $adherent = null)
    {
        $this->newsletter = $newsletter;
        $this->adherent = $adherent;
    }

    public function getNewsletter(): NewsletterSubscriptionInterface
    {
        return $this->newsletter;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }
}
