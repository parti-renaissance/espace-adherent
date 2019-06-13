<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailchimp\Manager;
use AppBundle\Mailchimp\Synchronisation\Command\AddNewsletterMemberCommand;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddNewsletterMemberCommandHandler implements MessageHandlerInterface
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

    public function __invoke(AddNewsletterMemberCommand $command): void
    {
        /** @var NewsletterSubscription|null $newsletter */
        $newsletter = $this->repository->find($command->getNewsletterSubscriptionId());

        if (!$newsletter) {
            return;
        }

        $this->entityManager->refresh($newsletter);

        if ($newsletter->isDeleted()) {
            return;
        }

        $this->manager->editNewsletterMember($newsletter);

        $this->entityManager->clear();
    }
}
