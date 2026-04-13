<?php

declare(strict_types=1);

namespace App\Newsletter\Handler;

use App\Entity\NewsletterSubscriptionInterface;
use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailchimp\Manager;
use App\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use App\Newsletter\NewsletterValueObject;
use App\Repository\Renaissance\NewsletterSourceRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailchimpSyncNewsletterSubscriptionEntityCommandHandler
{
    public function __construct(
        private readonly Manager $manager,
        private readonly ObjectManager $entityManager,
        private readonly NewsletterSourceRepository $newsletterSourceRepository,
    ) {
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

        if ($newsletter instanceof NewsletterSubscription) {
            $valueObject = NewsletterValueObject::createFromRenaissanceNewsletterSubscription($newsletter);

            if ($newsletter->source) {
                $source = $this->newsletterSourceRepository->findOneByCode($newsletter->source);

                if ($source && $source->mailchimpTag) {
                    $valueObject->addTag($source->mailchimpTag);
                }
            }
        } else {
            $valueObject = NewsletterValueObject::createFromNewsletterSubscription($newsletter);
        }

        $this->manager->editNewsletterMember($valueObject);

        $this->entityManager->clear();
    }
}
