<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mail\Transactional\NewsletterSubscriptionMail;
use Doctrine\ORM\EntityManagerInterface;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $mailPost;

    public function __construct(EntityManagerInterface $manager, MailPostInterface $mailPost)
    {
        $this->manager = $manager;
        $this->mailPost = $mailPost;
    }

    public function subscribe(NewsletterSubscription $subscription): void
    {
        $subscription = $this->recoverSoftDeletedSubscription($subscription);

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->mailPost->address(
            NewsletterSubscriptionMail::class,
            NewsletterSubscriptionMail::createRecipient($subscription),
            null,
            [],
            NewsletterSubscriptionMail::SUBJECT
        );
    }

    public function unsubscribe(?string $email): void
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
