<?php

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\ManageStaticSegmentCommand;
use App\AdherentMessage\StaticSegmentInterface;
use App\Mailchimp\Manager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ManageStaticSegmentCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $mailchimpManager;

    public function __construct(ObjectManager $entityManager, Manager $manager)
    {
        $this->entityManager = $entityManager;
        $this->mailchimpManager = $manager;
    }

    public function __invoke(ManageStaticSegmentCommand $command): void
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

        $repository = $this->entityManager->getRepository($command->getEntityClass());
        $emails = [];
        if (method_exists($repository, 'findMemberEmails')) {
            $emails = $this->entityManager
                ->getRepository($command->getEntityClass())
                ->findMemberEmails($command->getUuid()->toString())
            ;
        }

        if ($object->getMailchimpId()) {
            $this->mailchimpManager->addMembersToStaticSegment($object->getMailchimpId(), $emails);

            return;
        }

        if ($id = $this->mailchimpManager->createStaticSegment($object->getUuid()->toString(), null, $emails)) {
            $object->setMailchimpId($id);
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
