<?php

declare(strict_types=1);

namespace App\Newsletter\Handler;

use App\Entity\NewsletterSubscriptionInterface;
use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailchimp\Manager;
use App\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use App\Newsletter\NewsletterValueObject;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailchimpSyncNewsletterSubscriptionEntityCommandHandler
{
    private $manager;
    private $entityManager;

    public function __construct(Manager $manager, ObjectManager $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(MailchimpSyncNewsletterSubscriptionEntityCommand $command): void
    {
        /** @var NewsletterSubscriptionInterface|null $newsletter */
        $newsletter = $this->entityManager
            ->getRepository($command->getNewsletterSubscriptionClass())
            ->findById($command->getNewsletterSubscriptionId())
        ;

        if (!$newsletter) {
            return;
        }

        $this->entityManager->refresh($newsletter);

        $this->manager->editNewsletterMember(
            $newsletter instanceof NewsletterSubscription ?
                NewsletterValueObject::createFromRenaissanceNewsletterSubscription($newsletter) :
                NewsletterValueObject::createFromNewsletterSubscription($newsletter)
        );

        $this->entityManager->clear();
    }
}
