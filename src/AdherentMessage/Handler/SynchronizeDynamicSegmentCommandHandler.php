<?php

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\SynchronizeDynamicSegmentCommand;
use App\AdherentMessage\DynamicSegmentInterface;
use App\Mailchimp\Manager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SynchronizeDynamicSegmentCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $mailchimpManager;

    public function __construct(ObjectManager $entityManager, Manager $manager)
    {
        $this->entityManager = $entityManager;
        $this->mailchimpManager = $manager;
    }

    public function __invoke(SynchronizeDynamicSegmentCommand $command): void
    {
        /** @var DynamicSegmentInterface $object */
        $object = $this->entityManager
            ->getRepository($command->getEntityClass())
            ->findOneByUuid($command->getUuid()->toString())
        ;

        if (!$object) {
            return;
        }

        $this->entityManager->refresh($object);

        if ($segmentId = $object->getMailchimpId()) {
            $this->mailchimpManager->updateDynamicSegment($object, $segmentId);
        } else {
            $this->mailchimpManager->createDynamicSegment($object);
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
