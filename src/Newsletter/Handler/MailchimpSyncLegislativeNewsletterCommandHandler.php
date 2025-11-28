<?php

declare(strict_types=1);

namespace App\Newsletter\Handler;

use App\Entity\LegislativeNewsletterSubscription;
use App\Mailchimp\Manager;
use App\Newsletter\Command\MailchimpSyncLegislativeNewsletterCommand;
use App\Newsletter\NewsletterValueObject;
use App\Repository\LegislativeNewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MailchimpSyncLegislativeNewsletterCommandHandler
{
    private Manager $manager;
    private EntityManagerInterface $entityManager;
    private LegislativeNewsletterSubscriptionRepository $repository;

    public function __construct(
        Manager $manager,
        EntityManagerInterface $entityManager,
        LegislativeNewsletterSubscriptionRepository $repository,
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function __invoke(MailchimpSyncLegislativeNewsletterCommand $command): void
    {
        /** @var LegislativeNewsletterSubscription|null $newsletter */
        $newsletter = $this->repository->find($command->getSubscriptionId());

        if (!$newsletter) {
            return;
        }

        $this->entityManager->refresh($newsletter);

        $this->manager->editNewsletterMember(NewsletterValueObject::createFromLegislativeNewsletterCommand($newsletter));

        $this->entityManager->clear();
    }
}
