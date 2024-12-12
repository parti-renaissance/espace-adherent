<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentChangeCommandHandler implements LoggerAwareInterface
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
            $this->logger->warning(\sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($adherent);

        $this->manager->editMember($adherent, $message);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
