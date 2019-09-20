<?php

namespace AppBundle\Newsletter\Handler;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailchimp\Manager;
use AppBundle\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use AppBundle\Newsletter\NewsletterValueObject;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MailchimpSyncNewsletterSubscriptionEntityCommandHandler implements MessageHandlerInterface
{
    private $manager;
    private $entityManager;
    private $repository;

    public function __construct(
        Manager $manager,
        ObjectManager $entityManager,
        NewsletterSubscriptionRepository $repository
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function __invoke(MailchimpSyncNewsletterSubscriptionEntityCommand $command): void
    {
        /** @var NewsletterSubscription|null $newsletter */
        $newsletter = $this->repository
            ->disableSoftDeleteableFilter()
            ->find($command->getNewsletterSubscriptionId())
        ;

        if (!$newsletter) {
            return;
        }

        $this->entityManager->refresh($newsletter);

        $this->manager->editNewsletterMember(NewsletterValueObject::createFromNewsletterSubscription($newsletter));

        $this->entityManager->clear();

        $this->repository->enableSoftDeleteableFilter();
    }
}
