<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mail\Transactional\NewsletterSubscriptionMail;
use Doctrine\ORM\EntityManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $mailPost;

    public function __construct(EntityManager $manager, MailPostInterface $mailPost)
    {
        $this->manager = $manager;
        $this->mailPost = $mailPost;
    }

    public function subscribe(NewsletterSubscription $subscription)
    {
        $subscription = $this->recoverSoftDeletedSubscription($subscription);

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->mailPost->address(
            NewsletterSubscriptionMail::class,
            NewsletterSubscriptionMail::createRecipientFor($subscription)
        );
    }

    public function unsubscribe(?string $email)
    {
        $subscription = $this->findSubscriptionByEmail($email);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();
        }
    }

    private function recoverSoftDeletedSubscription(NewsletterSubscription $subscription): NewsletterSubscription
    {
        $this->manager->getFilters()->disable('softdeleteable');

        $softDeletedSubscription = $this->findSubscriptionByEmail($subscription->getEmail());

        $this->manager->getFilters()->enable('softdeleteable');

        if (!$softDeletedSubscription) {
            return $subscription;
        }

        if ($postalCode = $subscription->getPostalCode()) {
            $softDeletedSubscription->setPostalCode($postalCode);
        }

        $softDeletedSubscription->recover();

        return $softDeletedSubscription;
    }

    private function findSubscriptionByEmail(?string $email): ?NewsletterSubscription
    {
        return $this
            ->manager
            ->getRepository(NewsletterSubscription::class)
            ->findOneBy(['email' => $email])
        ;
    }
}
