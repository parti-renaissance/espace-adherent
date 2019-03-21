<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\CreateCommitteeStaticSegmentCommand;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateCommitteeStaticSegmentCommandHandler implements MessageHandlerInterface
{
    private $repository;
    private $entityManager;
    private $mailchimpManager;

    public function __construct(CommitteeRepository $repository, ObjectManager $entityManager, Manager $manager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->mailchimpManager = $manager;
    }

    public function __invoke(CreateCommitteeStaticSegmentCommand $command): void
    {
        $committee = $this->repository->findOneByUuid($command->getCommitteeUuid()->toString());

        if (!$committee) {
            return;
        }

        $this->entityManager->refresh($committee);

        if ($committee->getMailchimpId()) {
            return;
        }

        if ($id = $this->mailchimpManager->createStaticSegment($committee->getUuidAsString())) {
            $committee->setMailchimpId($id);
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
