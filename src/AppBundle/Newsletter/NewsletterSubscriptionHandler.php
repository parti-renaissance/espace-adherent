<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\NewsletterSubscriptionMessage;
use Doctrine\ORM\EntityManager;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $mailjet;

    public function __construct(EntityManager $manager, MailjetService $mailjet)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
    }

    public function subscribe(NewsletterSubscription $subscription)
    {
        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->mailjet->sendMessage(NewsletterSubscriptionMessage::createFromSubscription($subscription));
    }

    public function unsubscribe(?string $email)
    {
        $subscription = $this->manager->getRepository(NewsletterSubscription::class)->findOneBy(['email' => $email]);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();
        }
    }
}
