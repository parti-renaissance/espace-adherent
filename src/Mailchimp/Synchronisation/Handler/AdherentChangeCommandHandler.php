<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentChangeCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $repository;

    public function __construct(Manager $manager, AdherentRepository $repository, ObjectManager $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->logger = new NullLogger();
    }

    public function __invoke(AdherentChangeCommandInterface $message): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->repository->findOneByUuid($uuid = $message->getUuid()->toString())) {
            $this->logger->warning($error = sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($adherent);

        $this->manager->editMember($adherent, $message);

        $this->entityManager->clear();
    }
}
