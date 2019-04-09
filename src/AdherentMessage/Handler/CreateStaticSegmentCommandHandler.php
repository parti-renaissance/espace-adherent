<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\CreateStaticSegmentCommand;
use AppBundle\AdherentMessage\StaticSegmentInterface;
use AppBundle\Mailchimp\Manager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateStaticSegmentCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $mailchimpManager;

    public function __construct(ObjectManager $entityManager, Manager $manager)
    {
        $this->entityManager = $entityManager;
        $this->mailchimpManager = $manager;
    }

    public function __invoke(CreateStaticSegmentCommand $command): void
    {
        /** @var StaticSegmentInterface $object */
        $object = $this->entityManager
            ->getRepository($command->getEntityClass())
            ->findOneByUuid($command->getUuid()->toString())
        ;

        if (!$object) {
            return;
        }

        $this->entityManager->refresh($object);

        if ($object->getMailchimpId()) {
            return;
        }

        if ($id = $this->mailchimpManager->createStaticSegment($object->getUuid()->toString())) {
            $object->setMailchimpId($id);
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
