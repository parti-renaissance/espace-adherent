<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\NewsletterSubscriptionMessage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class NewsletterSubscriptionHandler
{
    private $entityManager;
    private $mailjet;

    public function __construct(EntityManager $entityManager, MailjetService $mailjet)
    {
        $this->entityManager = $entityManager;
        $this->mailjet = $mailjet;
    }

    public function handle(NewsletterSubscription $subscription, Request $request)
    {
        $subscription->setClientIp($request->getClientIp());

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        $this->mailjet->sendMessage(NewsletterSubscriptionMessage::createFromSubscription($subscription));
    }
}
