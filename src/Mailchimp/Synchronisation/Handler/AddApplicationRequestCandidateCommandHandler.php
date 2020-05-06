<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddApplicationRequestCandidateCommandHandler implements MessageHandlerInterface
{
    private $manager;
    private $entityManager;

    public function __construct(Manager $manager, ObjectManager $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AddApplicationRequestCandidateCommand $command): void
    {
        /** @var ApplicationRequest|null $applicationRequest */
        $applicationRequest = $this->entityManager
            ->getRepository($command->getType())
            ->find($command->getApplicationRequestId())
        ;

        if (!$applicationRequest) {
            return;
        }

        $this->entityManager->refresh($applicationRequest);

        $this->manager->editApplicationRequestCandidate($applicationRequest);

        $this->entityManager->clear();
    }
}
