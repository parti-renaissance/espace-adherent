<?php

declare(strict_types=1);

namespace App\Legislative\Newsletter;

use App\Entity\LegislativeNewsletterSubscription;
use Symfony\Contracts\EventDispatcher\Event;

class LegislativeNewsletterEvent extends Event
{
    private LegislativeNewsletterSubscription $newsletter;

    public function __construct(LegislativeNewsletterSubscription $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    public function getNewsletter(): LegislativeNewsletterSubscription
    {
        return $this->newsletter;
    }
}
