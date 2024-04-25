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
            $this->logger->warning(sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($adherent);

        // Avoid sync non-adherent user types
        if (
            null !== $adherent->getSource()
            && !$adherent->isJemengageUser()
            && !$adherent->isRenaissanceUser()
            && !$adherent->isBesoinDEuropeUser()
        ) {
            return;
        }

        $this->manager->editMember($adherent, $message);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
