<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use Symfony\Component\EventDispatcher\Event;

class NewsletterEvent extends Event
{
    private $newsletter;

    public function __construct(NewsletterSubscription $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    public function getNewsletter(): NewsletterSubscription
    {
        return $this->newsletter;
    }
}
