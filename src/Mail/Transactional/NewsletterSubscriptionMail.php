<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\NewsletterSubscription;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class NewsletterSubscriptionMail extends TransactionalMail
{
    public const SUBJECT = 'Je marche !';

    public static function createRecipient(NewsletterSubscription $subscription): RecipientInterface
    {
        return new Recipient($subscription->getEmail());
    }
}
