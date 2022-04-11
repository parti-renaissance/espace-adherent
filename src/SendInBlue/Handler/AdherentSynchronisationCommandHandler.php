<?php

namespace App\SendInBlue\Handler;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use App\SendInBlue\Command\AdherentSynchronisationCommand;
use App\SendInBlue\ContactManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentSynchronisationCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EntityManagerInterface $em;
    private AdherentRepository $adherentRepository;
    private ContactManager $contactManager;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        ContactManager $contactManager,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->contactManager = $contactManager;
        $this->logger = $logger;
    }

    public function __invoke(AdherentSynchronisationCommand $command): void
    {
        /** @var Adherent|null $adherent */
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent || !$adherent->isEnabled()) {
            return;
        }

        $this->em->refresh($adherent);

        try {
            $this->contactManager->synchronize($adherent, $command->getIdentifier());
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to synchronize adherent UUID: "%s". Error: %s', $adherent->getUuid(), $e->getMessage()));
        }
    }
}
