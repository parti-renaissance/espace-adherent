<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\AdherentMessage\StaticSegmentInterface;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateStaticSegmentCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
    ) {
    }

    public function __invoke(CreateStaticSegmentCommand $command): void
    {
        /** @var StaticSegmentInterface|null $object */
        $object = $this->entityManager
            ->getRepository($command->getEntityClass())
            ->findOneByUuid($command->getUuid())
        ;

        if (!$object) {
            return;
        }

        $this->entityManager->refresh($object);

        if ($object->getMailchimpId()) {
            return;
        }

        if ($id = $this->staticSegmentService->create($object->getUuid()->toString())) {
            $object->setMailchimpId($id);
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
