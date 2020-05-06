<?php

namespace App\Newsletter;

use App\Entity\Adherent;
use App\Entity\NewsletterSubscription;
use Symfony\Component\EventDispatcher\Event;

class NewsletterEvent extends Event
{
    private $newsletter;
    private $adherent;

    public function __construct(NewsletterSubscription $newsletter, Adherent $adherent = null)
    {
        $this->newsletter = $newsletter;
        $this->adherent = $adherent;
    }

    public function getNewsletter(): NewsletterSubscription
    {
        return $this->newsletter;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }
}
