<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\NewsletterSubscription;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class NewsletterSubscriptionMail extends TransactionalMail
{
    const SUBJECT = 'Je marche !';

    public static function createRecipientFor(NewsletterSubscription $subscription): RecipientInterface
    {
        return new Recipient($subscription->getEmail());
    }
}
